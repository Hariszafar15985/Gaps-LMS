<?php

namespace App\Http\Controllers\web;

use App\User;
use App\Models\File;
use App\Models\Sale;
use App\Models\Webinar;
use App\Models\Favorite;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\QuizzesResult;
use App\Models\WebinarReport;
use App\Helpers\WebinarHelper;
use App\Models\CourseLearning;
use App\Models\WebinarChapter;
use App\Mail\SendNotifications;
use App\Models\AdvertisingBanner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Models\Quiz;
use App\Models\WebinarChapterItem;

class WebinarController extends Controller
{
    use CheckContentLimitationTrait;
    use InstallmentsTrait;
    // public function course($slug, $courseContentOnly = false, $requestedUserId = null)
    // {
    //     $user = null;

    //     if ($courseContentOnly && isset($requestedUserId) && (int)$requestedUserId > 0) {
    //         $requestedUser = $user = User::find($requestedUserId);
    //     } elseif (auth()->check()) {
    //         $user = auth()->user();
    //     }

    //     $courseDataRequestedForProfile = true;
    //     if (isset($requestedUserId) && (int)$requestedUserId > 0) {
    //         $courseDataRequestedForProfile = false;
    //     }
    //     $webinarHelper = new WebinarHelper();
    //     $course = $webinarHelper->getCourseDataBySlug($slug, $user, $courseDataRequestedForProfile);

    //     if (empty($course)) {
    //         return back();
    //     }

    //     $isPrivate = $course->private;
    //     //TODO: re-evaluate this condition ($user->organ_id == $course->creator_id)
    //     if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin())) {
    //         $isPrivate = false;
    //     }

    //     if ($isPrivate) {
    //         return back();
    //     }

    //     $isFavorite = false;

    //     if (!empty($user)) {
    //         $isFavorite = Favorite::where('webinar_id', $course->id)
    //             ->where('user_id', $user->id)
    //             ->first();
    //     }

    //     $hasBought = $course->checkUserHasBought($user);

    //     $webinarContentCount = 0;
    //     if (!empty($course->sessions)) {
    //         $webinarContentCount += $course->sessions->count();
    //     }
    //     if (!empty($course->files)) {
    //         $webinarContentCount += $course->files->count();
    //     }
    //     if (!empty($course->textLessons)) {
    //         $webinarContentCount += $course->textLessons->count();
    //     }
    //     if (!empty($course->quizzes)) {
    //         $webinarContentCount += $course->quizzes->count();
    //     }

    //     $advertisingBanners = AdvertisingBanner::where('published', true)
    //         ->whereIn('position', ['course', 'course_sidebar'])
    //         ->get();

    //     $sessionChapters = $course->chapters->where('type', WebinarChapter::$chapterSession);
    //     $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

    //     $fileChapters = $course->chapters->where('type', WebinarChapter::$chapterFile);
    //     $filesWithoutChapter = $course->files->whereNull('chapter_id');

    //     $textLessonChapters = $course->chapters->where('type', WebinarChapter::$chapterTextLesson);
    //     $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

    //     $quizzes = $course->quizzes->whereNull('chapter_id');

    //     if ($user) {
    //         $quizzes = $this->checkQuizzesResults($user, $quizzes);

    //         if (!empty($course->chapters) and count($course->chapters)) {
    //             foreach ($course->chapters as $chapter) {
    //                 if (!empty($chapter->quizzes) and count($chapter->quizzes)) {
    //                     $chapter->quizzes = $this->checkQuizzesResults($user, $chapter->quizzes);
    //                 }
    //             }
    //         }
    //     }

    //     $data = [
    //         'pageTitle' => $course->title,
    //         'course' => $course,
    //         'isFavorite' => $isFavorite,
    //         'hasBought' => $hasBought,
    //         'user' => $user,
    //         'requestedUser' => $requestedUser ?? null,
    //         'webinarContentCount' => $webinarContentCount,
    //         'advertisingBanners' => $advertisingBanners->where('position', 'course'),
    //         'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),
    //         'activeSpecialOffer' => $course->activeSpecialOffer(),
    //         'sessionChapters' => $sessionChapters,
    //         'sessionsWithoutChapter' => $sessionsWithoutChapter,
    //         'fileChapters' => $fileChapters,
    //         'filesWithoutChapter' => $filesWithoutChapter,
    //         'textLessonChapters' => $textLessonChapters,
    //         'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
    //         'quizzes' => $quizzes,
    //         'courseContentRequestedOnly' => $courseContentOnly
    //     ];

    //     if ($courseContentOnly) {
    //         return (string) view('web.default.course.tabs.content', $data);
    //     } else {
    //         return view('web.default.course.index', $data);
    //     }
    // }
    public function course($slug, $justReturnData = false)
    {
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }


        if (!$justReturnData) {
            $contentLimitation = $this->checkContentLimitation($user, true);
            if ($contentLimitation != "ok") {
                return $contentLimitation;
            }
        }

        $course = Webinar::where('slug', $slug)
            ->with([
                'quizzes' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['quizResults', 'quizQuestions']);
                },
                'tags',
                'prerequisites' => function ($query) {
                    $query->with(['prerequisiteWebinar' => function ($query) {
                        $query->with(['teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }]);
                    }]);
                    $query->orderBy('order', 'asc');
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'webinarExtraDescription' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'chapters' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive);
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'quizzes' => function ($query) {
                            $query->where('status', 'active')
                                ->with(['quizResults', 'quizQuestions']);
                        },
                        'files' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    }
                                ]);
                        },
                        'textLessons' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->withCount(['attachments'])
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    },
                                    'quizzes' => function ($query) {
                                        $query->where('status', 'active');
                                    }
                                ]);
                        },
                        'sessions' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    }
                                ]);
                        },
                    ]);
                },
                'files' => function ($query) use ($user) {
                    $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                        ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                        ->where('files.status', WebinarChapter::$chapterActive)
                        ->orderBy('chapterOrder', 'asc')
                        ->orderBy('files.order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'textLessons' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->withCount(['attachments'])
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            },
                            'quizzes' => function ($query) {
                                $query->where('status', 'active');
                            }
                        ]);
                },
                'sessions' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'assignments' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive);
                },
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'filterOptions',
                'category',
                'teacher',
                'reviews' => function ($query) {
                    $query->where('status', 'active');
                    $query->with([
                        'comments' => function ($query) {
                            $query->where('status', 'active');
                        },
                        'creator' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }
                    ]);
                },
                'comments' => function ($query) {
                    $query->where('status', 'active');
                    $query->whereNull('reply_id');
                    $query->with([
                        'user' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                        },
                        'replies' => function ($query) {
                            $query->where('status', 'active');
                            $query->with([
                                'user' => function ($query) {
                                    $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                }
                            ]);
                        }
                    ]);
                    $query->orderBy('created_at', 'desc');
                },
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                },
                'noticeboards'
            ])
            ->where('status', 'active')
            ->first();


        if (empty($course)) {
            return $justReturnData ? false : back();
        }

        if (!$justReturnData) {
            $installmentLimitation = $this->installmentContentLimitation($user, $course->id, 'webinar_id');

            if ($installmentLimitation != "ok") {
                return $installmentLimitation;
            }
        }

        $hasBought = $course->checkUserHasBought($user, true, true);
        $isPrivate = $course->private;

        if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin())) {
            $isPrivate = false;
        }

        if ($isPrivate and $hasBought) { // check the user has bought the course or not
            $isPrivate = false;
        }

        if ($isPrivate) {
            return $justReturnData ? false : back();
        }

        $isFavorite = false;

        if (!empty($user)) {
            $isFavorite = Favorite::where('webinar_id', $course->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $webinarContentCount = 0;
        if (!empty($course->sessions)) {
            $webinarContentCount += $course->sessions->count();
        }
        if (!empty($course->files)) {
            $webinarContentCount += $course->files->count();
        }
        if (!empty($course->textLessons)) {
            $webinarContentCount += $course->textLessons->count();
        }
        if (!empty($course->quizzes)) {
            $webinarContentCount += $course->quizzes->count();
        }
        if (!empty($course->assignments)) {
            $webinarContentCount += $course->assignments->count();
        }

        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['course', 'course_sidebar'])
            ->get();

        $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

        $filesWithoutChapter = $course->files->whereNull('chapter_id');

        $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

        $sessionChapters = $course->chapters->where('type', WebinarChapter::$chapterSession);
        // $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

        $fileChapters = $course->chapters;

        // $filesWithoutChapter = $course->files->whereNull('chapter_id');

        // $textLessonChapters = $course->chapters->where('type', WebinarChapter::$chapterTextLesson);
        // $textLessonChapters = $course->textLessons;

        $courseDataRequestedForProfile = true;
        if (isset($justReturnData) && (int)$justReturnData > 0) {
            $courseDataRequestedForProfile = false;
        }
        $webinarHelper = new WebinarHelper();
        $course = $webinarHelper->getCourseDataBySlug($slug, $user, $courseDataRequestedForProfile);
        $textLessonChapters = $course->chapters;

        // $textLessonChapters = $course->chapters->textLessons;

        // $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');
        $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

        // $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');


        $quizzes = $course->quizzes->whereNull('chapter_id');

        //code to find the chapterItems of the course
        $chapterItems = WebinarHelper::getChapterItems($course);
        // dd($chapterItems[0]);

        if ($user) {
            $quizzes = $this->checkQuizzesResults($user, $quizzes);

            /* if (!empty($course->chapters) and count($course->chapters)) {
                foreach ($course->chapters as $chapter) {
                    if (!empty($chapter->chapterItems) and count($chapter->chapterItems)) {
                        foreach ($chapter->chapterItems as $chapterItem) {
                            if (!empty($chapterItem->quiz)) {
                                $chapterItem->quiz = $this->checkQuizResults($user, $chapterItem->quiz);
                            }
                        }
                    }
                }
            }

            if (!empty($course->quizzes) and count($course->quizzes)) {
                $course->quizzes = $this->checkQuizzesResults($user, $course->quizzes);
            } */
        }

        $pageRobot = getPageRobot('course_show'); // index
        $canSale = ($course->canSale() and !$hasBought);

        /* Installments */
        $showInstallments = true;
        $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

        if ($overdueInstallmentOrders->isNotEmpty() and getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
            $showInstallments = false;
        }

        if ($canSale and !empty($course->price) and $course->price > 0 and $showInstallments and getInstallmentsSettings('status') and (empty($user) or $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        /* Cashback Rules */
        if ($canSale and !empty($course->price) and getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $cashbackRules = $cashbackRulesMixin->getRules('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        $data = [
            'pageTitle' => $course->title,
            'pageDescription' => $course->seo_description,
            'pageRobot' => $pageRobot,
            'course' => $course,
            'isFavorite' => $isFavorite,
            'hasBought' => $hasBought,
            'user' => $user,
            'requestedUser' => $requestedUser ?? null,
            'webinarContentCount' => $webinarContentCount,
            'advertisingBanners' => $advertisingBanners->where('position', 'course'),
            'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),
            'activeSpecialOffer' => $course->activeSpecialOffer(),
            'sessionChapters' => $sessionChapters,
            'sessionsWithoutChapter' => $sessionsWithoutChapter,
            'fileChapters' => $fileChapters,
            'filesWithoutChapter' => $filesWithoutChapter,
            'textLessonChapters' => $textLessonChapters,
            'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
            'quizzes' => $quizzes,
            'installments' => $installments ?? null,
            'cashbackRules' => $cashbackRules ?? null,
            'courseContentRequestedOnly' => $justReturnData,
            'chapterItems' => $chapterItems
        ];
        // check for certificate
        if (!empty($user)) {
            $course->makeCourseCertificateForUser($user);
        }

        // if ($justReturnData) {
        //     return $data;
        // }

        // return view('web.default.course.index', $data);
        if ($justReturnData) {
            return (string) view('web.default.course.tabs.content', $data);
        } else {
            return view('web.default.course.index', $data);
        }
    }

    private function checkQuizzesResults($user, $quizzes)
    {
        $canDownloadCertificate = false;

        foreach ($quizzes as $quiz) {
            $canTryAgainQuiz = false;
            $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->get();

            if (count($userQuizDone)) {
                $quiz->user_grade = $userQuizDone->first()->user_grade;
                $quiz->result_status = $userQuizDone->first()->status;
                $quiz->result_count = $userQuizDone->count();
                $quiz->result = $userQuizDone->first();

                if ($quiz->result_status == 'passed') {
                    $canDownloadCertificate = true;
                }
            }

            if (!isset($quiz->attempt) or (count($userQuizDone) < $quiz->attempt and $quiz->result_status !== 'pass')) {
                $canTryAgainQuiz = true;
            }

            $quiz->can_try = $canTryAgainQuiz;
            $quiz->can_download_certificate = $canDownloadCertificate;
        }

        return $quizzes;
    }

    public function downloadFile($slug, $file_id)
    {
        $webinar = Webinar::where('slug', $slug)
            ->where('private', false)
            ->where('status', 'active')
            ->first();

        if (!empty($webinar)) {
            $file = File::where('webinar_id', $webinar->id)
                ->where('id', $file_id)
                ->first();

            if (!empty($file) and $file->downloadable) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    $filePath = public_path($file->file);

                    $fileName = str_replace(' ', '-', $file->title);
                    $fileName = str_replace('.', '-', $fileName);
                    $fileName .= '.' . $file->file_type;

                    $headers = array(
                        'Content-Type: application/' . $file->file_type,
                    );

                    return response()->download($filePath, $fileName, $headers);
                } else {
                    $toastData = [
                        'title' => trans('public.not_access_toast_lang'),
                        'msg' => trans('public.not_access_toast_msg_lang'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }
            }
        }

        return back();
    }

    public function getFilePath(Request $request)
    {
        $this->validate($request, [
            'file_id' => 'required'
        ]);
        $file_id = $request->get('file_id');

        $file = File::where('id', $file_id)->first();
        // dd($file);

        if (!empty($file)) {
            $webinar = Webinar::where('id', $file->webinar_id)
                ->where('status', 'active')
                ->with([
                    'files' => function ($query) {
                        $query->select('id', 'webinar_id', 'file_type')
                            ->where('status', 'active')
                            ->orderBy('order', 'asc');
                    }
                ])
                ->first();
            // dd($webinar);
            if (!empty($webinar)) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }
                // dd($canAccess);
                if ($canAccess) {
                    $storageService = $file->getFileStorageService();
                    // dd($storageService);
                    return response()->json([
                        'code' => 200,
                        'storage' => $file->storage,
                        'path' => ($file->storage == 'local' || $file->storage == 'upload') ? url("/course/$webinar->slug/file/$file->id/play") : $file->file,
                        'storageService' => $storageService
                    ], 200);
                }
            }
        }

        abort(403);
    }

    // public function playFile($slug, $file_id)
    // {
    //     $webinar = Webinar::where('slug', $slug)
    //         ->where('private', false)
    //         ->where('status', 'active')
    //         ->first();
    //     if (!empty($webinar)) {
    //         $file = File::where('webinar_id', $webinar->id)
    //             ->where('id', $file_id)
    //             ->first();

    //         if (!empty($file) and $file->isVideo()) {
    //             $canAccess = true;

    //             if ($file->accessibility == 'paid') {
    //                 $canAccess = $webinar->checkUserHasBought();
    //             }

    //             if ($canAccess) {
    //                 return response()->file(public_path($file->file));
    //             }
    //         }
    //     }

    //     abort(403);
    // }
    public function playFile($slug, $file_id)
    {
        // this methode linked from video modal for play local video
        // and linked from file.blade for show google_drive,dropbox,iframe

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!empty($course) and $this->checkCanAccessToPrivateCourse($course)) {
            $file = File::where('webinar_id', $course->id)
                ->where('id', $file_id)
                ->first();
            if (!empty($file)) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $course->checkUserHasBought();
                }

                // next and previous contents of the course
                $chapterItems = WebinarHelper::getChapterItems($course);
                $previous = WebinarHelper::getPreviousItem($file ,$chapterItems, 'file');
                $next = WebinarHelper::getNextItem($file ,$chapterItems ,'file');

                if ($canAccess) {
                    $notVideoSource = ['iframe', 'google_drive', 'dropbox'];

                    if (in_array($file->storage, $notVideoSource)) {

                        $data = [
                            'pageTitle' => $file->title,
                            'iframe' => $file->file,
                            'course' => $course,
                            'next' => $next,
                            'previous' => $previous
                        ];

                        return view('web.default.course.learningPage.interactive_file', $data);
                    } else if ($file->isVideo()) {

                        if ($file->storage == 'youtube') {
                            $data = [
                                'file' => $file,
                                'pageTitle' => $file->title,
                                'iframe' => $file->file,
                                'course' => $course,
                                'next' => $next,
                                'previous' => $previous
                            ];
                        } else {

                            $data = [
                                'file' => $file,
                                'pageTitle' => $file->title,
                                // 'iframe' => public_path($file->file),
                                'videoPath' => $file->file,
                                'course' => $course,
                                'next' => $next,
                                'previous' => $previous
                            ];
                        }
                        return view('web.default.course.learningPage.interactive_file', $data);

                    } elseif ($file->storage == 'upload') {
                        $data = [
                            'file' => $file,
                            'pageTitle' => $file->title,
                            'filePath' => $file->file,
                            'course' => $course,
                            'next' => $next,
                            'previous' => $previous
                        ];

                        return view('web.default.course.learningPage.interactive_file', $data);
                    }
                }
            }
        }

        abort(403);
    }
    public function showHtmlFile($slug, $file_id)
    {
        $webinar = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();
        if (!empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
            $file = File::where('webinar_id', $webinar->id)
            ->where('id', $file_id)->where('storage', 'upload_archive')
            ->first();


            if (!empty($file)) {
                $canAccess = true;
                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                // next and previous content of course
                $chapterItems = WebinarHelper::getChapterItems($webinar);
                $previous = WebinarHelper::getPreviousItem($file ,$chapterItems, 'file');
                $next = WebinarHelper::getNextItem($file ,$chapterItems ,'file');

                //new request object created to pass request data to the learningStatus function
                $newRequest = Request::create(route('webinar.learningStatus',$webinar->id), 'POST', ['item' => 'file_id','item_id' => $file->id, 'status' => true]);
                $this->learningStatus($newRequest ,$webinar->id);

                if ($canAccess) {
                    $filePath = $file->interactive_file_path;


                    if (\Illuminate\Support\Facades\File::exists(public_path($filePath))) {
                        $data = [
                            'file' => $file,
                            'course' => $webinar,
                            'pageTitle' => $file->title,
                            'path' => url($filePath),
                            'previous' => $previous,
                            'next' => $next
                        ];
                        return view('web.default.course.learningPage.interactive_file', $data);
                    }

                    abort(404);
                } else {
                    $toastData = [
                        'title' => trans('public.not_access_toast_lang'),
                        'msg' => trans('public.not_access_toast_msg_lang'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }
            }
        }

        abort(403);
    }
    public function getLesson(Request $request, $slug, $lesson_id)
    {
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }

        $webinarHelper = new WebinarHelper();
        $course = $webinarHelper->getCourseDataBySlug($slug, $user);
        if (!empty($course)) {
            $textLessonChapters = $course->chapters;
        }

        $query = Webinar::where('slug', $slug)
            ->where('private', false)
            ->with(['teacher', 'textLessons' => function ($query) {
                $query->orderBy('order', 'asc');
            }]);

        //only admin should be able to preview courses that aren't active
        if (empty($user) || !$user->isAdmin()) {
            $query = $query->where('status', 'active');
            $course = $query->first();
        }

        if (!empty($course)) {
            $textLesson = TextLesson::where('id', $lesson_id)
                ->where('webinar_id', $course->id)
                ->with([
                    'attachments' => function ($query) {
                        $query->with('file');
                    },
                    'learningStatus' => function ($query) use ($user) {
                        $query->where('user_id', !empty($user) ? $user->id : null);
                    },
                    'audioFile' => function ($query) {
                        $query->first();
                    },
                ])
                ->first();

            if (!empty($textLesson)) {
                $canAccess =  (!$user->isUser() || ($user->isUser() && $course->checkUserHasBought()));
                if ($textLesson->accessibility == 'paid' and !$canAccess) {
                    return back();
                }
                //auto-mark lesson as read
                $request->merge([
                    'item' => 'text_lesson_id',
                    'item_id' => $lesson_id,
                    'status' => "true"
                ]);

                $this->learningStatus($request, $course->id);

                $webinarHelper->courseCompletionNotification($course);

                //next and previous items of course
                $chapterItems = WebinarHelper::getChapterItems($course);
                $previous = WebinarHelper::getPreviousItem($textLesson ,$chapterItems, 'text_lesson');
                $next = WebinarHelper::getNextItem($textLesson ,$chapterItems ,'text_lesson');

                $data = [
                    'pageTitle' => $textLesson->title,
                    'textLesson' => $textLesson,
                    'course' => $course,
                    'next' => $next,
                    'previous' => $previous,
                    'textLessonChapters' => $textLessonChapters,
                    "audioFile" => $textLesson->audioFile()->first()
                ];

                return view(getTemplate() . '.course.text_lesson', $data);
            }
        }

        abort(404);
    }
    // public function getLesson(Request $request, $slug, $lesson_id)
    // {
    //     $user = null;

    //     if (auth()->check()) {
    //         $user = auth()->user();
    //     }

    //     $course = Webinar::where('slug', $slug)
    //         ->where('status', 'active')
    //         ->with(['teacher', 'textLessons' => function ($query) {
    //             $query->orderBy('order', 'asc');
    //         }])
    //         ->first();
    //     if (!empty($course)) {
    //         $textLessonChapters = $course->chapters->where('type', WebinarChapter::$chapterTextLesson);
    //     }
    //     if (!empty($course) and $this->checkCanAccessToPrivateCourse($course)) {
    //         $textLesson = TextLesson::where('id', $lesson_id)
    //             ->where('webinar_id', $course->id)
    //             ->where('status', WebinarChapter::$chapterActive)
    //             ->with([
    //                 'attachments' => function ($query) {
    //                     $query->with('file');
    //                 },
    //                 'learningStatus' => function ($query) use ($user) {
    //                     $query->where('user_id', !empty($user) ? $user->id : null);
    //                 }
    //             ])
    //             ->first();

    //         if (!empty($textLesson)) {
    //             $canAccess = $course->checkUserHasBought();

    //             if ($textLesson->accessibility == 'paid' and !$canAccess) {
    //                 $toastData = [
    //                     'title' => trans('public.request_failed'),
    //                     'msg' => trans('cart.you_not_purchased_this_course'),
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //             }

    //             $checkSequenceContent = $textLesson->checkSequenceContent();
    //             $sequenceContentHasError = (!empty($checkSequenceContent) and (!empty($checkSequenceContent['all_passed_items_error']) or !empty($checkSequenceContent['access_after_day_error'])));

    //             if (!empty($checkSequenceContent) and $sequenceContentHasError) {
    //                 $toastData = [
    //                     'title' => trans('public.request_failed'),
    //                     'msg' => ($checkSequenceContent['all_passed_items_error'] ? $checkSequenceContent['all_passed_items_error'] . ' - ' : '') . ($checkSequenceContent['access_after_day_error'] ?? ''),
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //             }

    //             $nextLesson = null;
    //             $previousLesson = null;
    //             if (!empty($course->textLessons) and count($course->textLessons)) {
    //                 $nextLesson = $course->textLessons->where('order', '>', $textLesson->order)->first();
    //                 $previousLesson = $course->textLessons->where('order', '<', $textLesson->order)->first();
    //             }

    //             if (!empty($nextLesson)) {
    //                 $nextLesson->not_purchased = ($nextLesson->accessibility == 'paid' and !$canAccess);
    //             }


    //             $data = [
    //                 'pageTitle' => $textLesson->title,
    //                 'textLesson' => $textLesson,
    //                 'course' => $course,
    //                 'nextLesson' => $nextLesson,
    //                 'previousLesson' => $previousLesson,
    //                 'textLessonChapters' => $textLessonChapters,
    //                 "audioFile" => $textLesson->audioFile()->first()
    //             ];

    //             return view(getTemplate() . '.course.text_lesson', $data);
    //         }
    //     }

    //     abort(404);
    // }
    public function free(Request $request, $slug)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($course)) {
                $checkCourseForSale = checkCourseForSale($course, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $course->creator_id,
                    'webinar_id' => $course->id,
                    'type' => Sale::$webinar,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
                ]);

                $toastData = [
                    'title' => '',
                    'msg' => trans('cart.success_pay_msg_for_free_course'),
                    'status' => 'success'
                ];

                if (env('APP_ENV') == 'production') {
                    $title = $course->slug;
                    $message = trans('cart.success_pay_msg_for_free_course');
                    Mail::to($user->email)
                        ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                    Log::channel('mail')->debug('Web - Course Enroll Mail sent to : ' . $user->email);
                }

                return back()->with(['toast' => $toastData]);
            }

            abort(404);
        } else {
            return redirect('/login');
        }
    }

    public function reportWebinar(Request $request, $id)
    {
        if (auth()->check()) {
            $user_id = auth()->id();

            $this->validate($request, [
                'reason' => 'required|string',
                'message' => 'required|string',
            ]);

            $data = $request->all();

            $webinar = Webinar::select('id', 'status')
                ->where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!empty($webinar)) {
                WebinarReport::create([
                    'user_id' => $user_id,
                    'webinar_id' => $webinar->id,
                    'reason' => $data['reason'],
                    'message' => $data['message'],
                    'created_at' => time()
                ]);

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([
            'code' => 401
        ], 200);
    }

    public function learningStatus(Request $request, $id)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $course = Webinar::where('id', $id)->first();

            if (
                !empty($course)
                && ($user->isAdmin() //admin is accessing the text lesson
                    || $user->isTeacher() //teacher is accessing the text lesson
                    || ($user->isUser() && $course->checkUserHasBought($user)) //enrolled student is accessing
                )
            ) {
                $data = $request->all();

                $item = $data['item'];
                $item_id = $data['item_id'];
                $status = $data['status'];

                CourseLearning::where('user_id', $user->id)
                    ->where($item, $item_id)
                    ->delete();

                if ($status and $status == "true") {
                    CourseLearning::create([
                        'user_id' => $user->id,
                        $item => $item_id,
                        'created_at' => time()
                    ]);
                }

                return response()->json([], 200);
            }
        }

        abort(403);
    }

    private function checkCanAccessToPrivateCourse($course, $user = null): bool
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        $canAccess = !$course->private;
        $hasBought = $course->checkUserHasBought($user);

        if (!empty($user) and ($user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() or $hasBought)) {
            $canAccess = true;
        }

        return $canAccess;
    }

    // public function getPreviousItem($currentItem ,$webinar)
    // {
    //     $chapterItems = [];
    //     $previous = null;
    //     $textLessonQuizzess = null;
    //     foreach ($webinar->chapters as $index => $chapter) {
    //         foreach ($chapter->chapterItems as $item) {
    //             $chapterItems[]  = $item;
    //         }
    //     }

    //     foreach ($chapterItems as $index => $chapItem) {

    //         if ($chapItem->item_id == $currentItem->id && $index > 0) {

    //             $previousItem = $chapterItems[$index - 1];
    //             // dd($previousItem);

    //             if ($previousItem->type == 'text_lesson') {

    //                 $textLessonItem = TextLesson::find($previousItem->item_id);
    //                 if ($textLessonItem && $textLessonItem->status == 'active') {

    //                     if (!empty($textLessonItem->quizzes) && count($textLessonItem->quizzes) > 0) {
    //                         $textLessonQuizzess = $textLessonItem->quizzes->toArray();
    //                         $previousQuiz = $textLessonQuizzess[count($textLessonQuizzess)-1];
    //                         if ($previousQuiz) {
    //                             $previous = WebinarChapterItem::where('item_id', $previousQuiz['id'])->where('type','quiz')->first();
    //                         }

    //                     } else {

    //                         $previous = $previousItem;
    //                     }
    //                 }
    //             } elseif ($previousItem->type == 'file') {

    //                 $fileItem = File::find($previousItem->item_id);
    //                 if ($fileItem && $fileItem->status == 'active') {
    //                     $previous = $previousItem;
    //                 }

    //             } elseif ($previousItem->type == 'quiz') {

    //                 $quizItem = Quiz::find($previousItem->item_id);
    //                 if (($quizItem && $quizItem->status == 'active') ) {

    //                     if ($quizItem->text_lesson_id) {
    //                         $quizTextLesson = TextLesson::find($quizItem->text_lesson_id);
    //                         $textLessonQuizzess = $quizTextLesson->quizzes->toArray();
    //                         if (!empty($textLessonQuizzess) && count($textLessonQuizzess) > 0) {
    //                             $previousQuiz = Quiz::where('text_lesson_id',$quizItem->text_lesson_id)->where('id','<' , $quizItem->id);
    //                             $previous = WebinarChapterItem::where('item_id', $previousQuiz->id)->where('type','quiz')->first();
    //                         }
    //                     } else {

    //                         $previous = $previousItem;
    //                     }
    //                 }
    //             } else {
    //                 $previous = $previous;
    //             }
    //         }
    //     }

    //     return $previous;
    // }

    // public function getNextItem ($currentItem ,$webinar)
    // {
    //     $chapterItems = [];
    //     $next = null;
    //     $textLessonQuizzess = null;
    //     foreach ($webinar->chapters as $index => $chapter) {
    //         foreach ($chapter->chapterItems as $item) {
    //             $chapterItems[]  = $item;
    //         }
    //     }

    //     foreach ($chapterItems as $index => $chapItem) {

    //         if (($chapItem->item_id == $currentItem->id) && ( $index > 0 && $index < count($chapterItems))) {

    //             $nextItem = $chapterItems[$index + 1];
    //             // dd($nextItem);

    //             if ($nextItem->type == 'text_lesson') {

    //                 $textLessonItem = TextLesson::find($nextItem->item_id);
    //                 if ($textLessonItem && $textLessonItem->status == 'active') {

    //                     if (!empty($textLessonItem->quizzes) && count($textLessonItem->quizzes) > 0) {
    //                         $textLessonQuizzess = $textLessonItem->quizzes->toArray();
    //                         $nextQuiz = $textLessonQuizzess[0];
    //                         if ($nextQuiz) {
    //                             $next = WebinarChapterItem::where('item_id', $nextQuiz['id'])->where('type','quiz')->first();
    //                         }

    //                     } else {

    //                         $next = $nextItem;
    //                     }
    //                 }
    //             } elseif ($nextItem->type == 'file') {

    //                 $fileItem = File::find($nextItem->item_id);
    //                 if ($fileItem && $fileItem->status == 'active') {
    //                     $next = $nextItem;
    //                 }

    //             } elseif ($nextItem->type == 'quiz') {

    //                 $quizItem = Quiz::find($nextItem->item_id);
    //                 if (($quizItem && $quizItem->status == 'active') ) {

    //                     if ($quizItem->text_lesson_id) {
    //                         $quizTextLesson = TextLesson::find($quizItem->text_lesson_id);
    //                         $textLessonQuizzess = $quizTextLesson->quizzes->toArray();
    //                         if (!empty($textLessonQuizzess) && count($textLessonQuizzess) > 0) {
    //                             $nextQuiz = Quiz::where('text_lesson_id',$quizItem->text_lesson_id)->where('id','>' , $quizItem->id);
    //                             $next = WebinarChapterItem::where('item_id', $nextQuiz->id)->where('type','quiz')->first();
    //                         }
    //                     } else {

    //                         $next = $nextItem;
    //                     }
    //                 }
    //             } else {
    //                 $next = $next;
    //             }
    //         }
    //     }

    //     return $next;
    // }
}
