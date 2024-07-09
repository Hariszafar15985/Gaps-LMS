<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Exception;
use App\Models\Tag;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Reward;
use App\Models\Ticket;
use App\Models\Webinar;
use App\Models\Category;
use App\CourseVisibility;
use App\Models\AuditTrail;
use App\Models\TextLesson;
use App\Models\Api\Session;
use App\Models\SpecialOffer;
use Illuminate\Http\Request;
use App\Models\BundleWebinar;
use App\Models\WebinarChapter;
use App\Exports\WebinarsExport;
use App\Http\Controllers\Admin\traits\WebinarChangeCreator;
use App\Models\QuizzesQuestion;
use App\Models\RewardAccounting;
use App\Models\WebinarAssignment;
use App\Models\WebinarChapterItem;
use Illuminate\Support\Facades\DB;
use App\Models\WebinarFilterOption;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\TextLessonAttachment;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WebinarPartnerTeacher;
use App\Models\QuizzesQuestionsAnswer;
use Illuminate\Support\Facades\Validator;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Traits\UserCourseRegistrationTrait;
use App\Models\Translation\WebinarTranslation;
use App\Models\Translation\TextLessonTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\SessionTranslation;
use App\Models\Translation\WebinarAssignmentTranslation;
use PayPal\Api\FileAttachment;

class WebinarController extends Controller
{
    use UserCourseRegistrationTrait;
    use WebinarChangeCreator;

    public function index(Request $request)
    {
        $this->authorize('admin_webinars_list');

        removeContentLocale();

        $type = $request->get('type', 'webinar');
        $query = Webinar::where('webinars.type', $type);

        $totalWebinars = $query->count();
        $totalPendingWebinars = deepClone($query)->where('webinars.status', 'pending')->count();
        $totalDurations = deepClone($query)->sum('duration');
        $totalSales = deepClone($query)->join('sales', 'webinars.id', '=', 'sales.webinar_id')
            ->select(DB::raw('count(sales.webinar_id) as sales_count'))
            ->whereNotNull('sales.webinar_id')
            ->whereNull('sales.refund_at')
            ->first();

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $inProgressWebinars = 0;
        if ($type == 'webinar') {
            $inProgressWebinars = $this->getInProgressWebinarsCount();
        }

        $query = $this->filterWebinar($query, $request)
            ->with([
                'category',
                'teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ]);

        $webinars = $query->paginate(10);

        if ($request->get('status', null) == 'active_finished') {
            foreach ($webinars as $key => $webinar) {
                if ($webinar->last_date > time()) { // is in progress
                    unset($webinars[$key]);
                }
            }
        }

        $data = [
            'pageTitle' => trans('admin/pages/webinars.webinars_list_page_title'),
            'webinars' => $webinars,
            'totalWebinars' => $totalWebinars,
            'totalPendingWebinars' => $totalPendingWebinars,
            'totalDurations' => $totalDurations,
            'totalSales' => !empty($totalSales) ? $totalSales->sales_count : 0,
            'categories' => $categories,
            'inProgressWebinars' => $inProgressWebinars,
            'classesType' => $type,
        ];

        $teacher_ids = $request->get('teacher_ids', null);
        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        return view('admin.webinars.lists', $data);
    }

    private function filterWebinar($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $title = $request->get('title', null);
        $teacher_ids = $request->get('teacher_ids', null);
        $category_id = $request->get('category_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->whereTranslationLike('title', '%' . $title . '%');
        }

        if (!empty($teacher_ids) and count($teacher_ids)) {
            $query->whereIn('teacher_id', $teacher_ids);
        }

        if (!empty($category_id)) {
            $query->where('category_id', $category_id);
        }

        if (!empty($status)) {
            $time = time();

            switch ($status) {
                case 'active_not_conducted':
                    $query->where('status', 'active')
                        ->where('start_date', '>', $time);
                    break;
                case 'active_in_progress':
                    $query->where('status', 'active')
                        ->where('start_date', '<=', $time)
                        ->join('sessions', 'webinars.id', '=', 'sessions.webinar_id')
                        ->select('webinars.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.webinar_id')
                        ->where('sessions.date', '>', $time);
                    break;
                case 'active_finished':
                    $query->where('status', 'active')
                        ->where('start_date', '<=', $time)
                        ->join('sessions', 'webinars.id', '=', 'sessions.webinar_id')
                        ->select('webinars.*', 'sessions.date', DB::raw('max(`date`) as last_date'))
                        ->groupBy('sessions.webinar_id');
                    break;
                default:
                    $query->where('status', $status);
                    break;
            }
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'has_discount':
                    $now = time();
                    $webinarIdsHasDiscount = [];

                    $tickets = Ticket::where('start_date', '<', $now)
                        ->where('end_date', '>', $now)
                        ->get();

                    foreach ($tickets as $ticket) {
                        if ($ticket->isValid()) {
                            $webinarIdsHasDiscount[] = $ticket->webinar_id;
                        }
                    }

                    $specialOffersWebinarIds = SpecialOffer::where('status', 'active')
                        ->where('from_date', '<', $now)
                        ->where('to_date', '>', $now)
                        ->pluck('webinar_id')
                        ->toArray();

                    $webinarIdsHasDiscount = array_merge($specialOffersWebinarIds, $webinarIdsHasDiscount);

                    $query->whereIn('id', $webinarIdsHasDiscount)
                        ->orderBy('created_at', 'desc');
                    break;
                case 'sales_asc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.webinar_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_desc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.webinar_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('sales_count', 'desc');
                    break;

                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'income_asc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('amounts', 'asc');
                    break;

                case 'income_desc':
                    $query->join('sales', 'webinars.id', '=', 'sales.webinar_id')
                        ->select('webinars.*', 'sales.webinar_id', 'sales.total_amount', 'sales.refund_at', DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as amounts'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.webinar_id')
                        ->orderBy('amounts', 'desc');
                    break;

                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;

                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'updated_at_asc':
                    $query->orderBy('updated_at', 'asc');
                    break;

                case 'updated_at_desc':
                    $query->orderBy('updated_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        return $query;
    }

    private function getInProgressWebinarsCount()
    {
        $count = 0;
        $webinars = Webinar::where('type', 'webinar')
            ->where('status', 'active')
            ->where('start_date', '<=', time())
            ->whereHas('sessions')
            ->get();

        foreach ($webinars as $webinar) {
            if ($webinar->isProgressing()) {
                $count += 1;
            }
        }

        return $count;
    }

    public function create()
    {
        $this->authorize('admin_webinars_create');

        removeContentLocale();

        $teachers = User::where('role_name', Role::$teacher)->get();
        $categories = Category::where('parent_id', null)->get();
        $query = User::where('role_name', Role::$organization)->where('status', 'active')->get();

        $data = [
            'pageTitle' => trans('admin/main.webinar_new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories,
            'organization' => $query,
        ];

        return view('admin.webinars.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_webinars_create');
        if (!$request->visible_to_all) {
            $this->validate($request, [
                'organizations' => 'required',
            ]);
        }

        $this->validate($request, [
            'webinar_type' => 'required|in:webinar,course,text_lesson',
            'web_title' => 'required|max:255',
            'slug' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
            'duration' => 'required',
            'start_date' => 'required_if:type,webinar',
            'capacity' => 'required_if:type,webinar',
        ]);

        $data = $request->all();

        $webinar = Webinar::create([
            'type' => $data['webinar_type'],
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $data['teacher_id'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'capacity' => $data['capacity'] ?? null,
            'start_date' => (!empty($data['start_date'])) ? strtotime($data['start_date']) : null,
            'duration' => $data['duration'] ?? null,
            'duration_week' => $data['duration_week'] ?? null,
            'support' => !empty($data['support']) ? true : false,
            'downloadable' => !empty($data['downloadable']) ? true : false,
            'partner_instructor' => !empty($data['partner_instructor']) ? true : false,
            'subscribe' => !empty($data['subscribe']) ? true : false,
            'price' => $data['price'],
            'visible_to_all' => (isset($data['visible_to_all']) && $data['visible_to_all']) ? true : false,
            'category_id' => $data['category_id'],
            'status' => Webinar::$pending,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        if ($webinar) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['web_title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            foreach ($filters as $filter) {
                WebinarFilterOption::create([
                    'webinar_id' => $webinar->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('webinar_id', $webinar->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'webinar_id' => $webinar->id,
                    'title' => $tag,
                ]);
            }
        }

        if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

            foreach ($request->get('partners') as $partnerId) {
                WebinarPartnerTeacher::create([
                    'webinar_id' => $webinar->id,
                    'teacher_id' => $partnerId,
                ]);
            }
        }

        if ($webinar && !$request->has("visible_to_all") && $request->has("organizations")) {
            $organizations = $request->organizations;
            foreach ($organizations as $organizationId) {
                CourseVisibility::create([
                    "course_id" => $webinar->id,
                    "organization_id" => $organizationId,
                ]);
            }
        }

        return redirect('/admin/webinars/' . $webinar->id . '/edit');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinar = Webinar::where('id', $id)
            ->with([
                'tickets',
                'sessions',
                'files',
                'chapters' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'faqs',
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'filterOptions',
                'prerequisites',
                'quizzes' => function ($query) {
                    $query->with([
                        'quizQuestions' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }
                    ]);
                },
                'webinarPartnerTeacher' => function ($query) {
                    $query->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }]);
                },
                'tags',
                'textLessons',
                'assignments',
                'chapters' => function ($query) {
                    $query->orderBy('order', 'asc');
                    $query->with([
                        'chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                            $query->with([
                                'quiz' => function ($query) {
                                    $query->with([
                                        'quizQuestions' => function ($query) {
                                            $query->orderBy('order', 'asc');
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                },
            ])
            ->first();

        if (empty($webinar)) {
            abort(404);
        }

        // to get all webinars of the same type of webinar that is being updated
        $webinars = Webinar::where('type', $webinar->type)->get();

        $locale = $request->get('locale', app()->getLocale());
        storeContentLocale($locale, $webinar->getTable(), $webinar->id);

        $teachers = User::where('role_name', Role::$teacher)->get();
        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $teacherQuizzes = Quiz::
            // where('webinar_id', null)
            where('creator_id', $webinar->teacher_id)
            ->get();

        $tags = $webinar->tags->pluck('title')->toArray();
        $organizationsList = User::where('role_name', Role::$organization)->where('status', 'active')->orderBy('full_name', 'ASC')->get();
        $courseVisibility = CourseVisibility::where("course_id", $webinar->id)->pluck("organization_id");

        $data = [
            'pageTitle' => trans('admin/main.edit') . ' | ' . $webinar->title,
            'teachers' => $teachers,
            'categories' => $categories,
            'webinar' => $webinar,
            'webinarCategoryFilters' => !empty($webinar->category) ? $webinar->category->filters : null,
            'webinarFilterOptions' => $webinar->filterOptions->pluck('filter_option_id')->toArray(),
            'tickets' => $webinar->tickets,
            'chapters' => $webinar->chapters,
            'sessions' => $webinar->sessions,
            'files' => $webinar->files,
            'textLessons' => $webinar->textLessons,
            'faqs' => $webinar->faqs,
            'assignments' => $webinar->assignments,
            'teacherQuizzes' => $teacherQuizzes,
            'prerequisites' => $webinar->prerequisites,
            'webinarQuizzes' => $webinar->quizzes,
            // 'webinarPartnerTeacher' => $webinar->webinarPartnerTeacher->keyby('teacher_id'),
            'webinarPartnerTeacher' => $webinar->webinarPartnerTeacher,
            'webinarTags' => $tags,
            'locale' => $locale ?? null,
            'defaultLocale' => getDefaultLocale(),
            "organization" => $organizationsList,
            "courseVisibility" => $courseVisibility->toArray(),
            "webinars" =>  $webinars
        ];

        return view('admin.webinars.create', $data);
    }

    // public function duplicate ($id) {
            // if (!empty($webinar)) {
            //Get chapters against source course
        //     $chapters = WebinarChapter::where('webinar_id', $id)->get()->toArray();
            //webinar to be coppied
        //     $webinar = Webinar::where('id', $id)->first();
        //     $new = $webinar->toArray();

        //     $title = $webinar->title . ' ' . trans('public.copy');
        //     $description = $webinar->description;
        //     $seo_description = $webinar->seo_description;


        //     $new['created_at'] = time();
        //     $new['updated_at'] = time();
        //     $new['creator_id'] = $user->id;
        //     $new['status'] = Webinar::$pending;

        //     $new['slug'] = Webinar::makeSlug($title);

        //     foreach ($webinar->translatedAttributes as $attribute) {
        //         unset($new[$attribute]);
        //     }

        //     unset($new['translations']);

        //     $newWebinar = Webinar::create($new);

        //     WebinarTranslation::updateOrCreate([
        //         'webinar_id' => $newWebinar->id,
        //         'locale' => mb_strtolower($webinar->locale),
        //     ], [
        //         'title' => $title,
        //         'description' => $description,
        //         'seo_description' => $seo_description,
        //     ]);

        //     //Get chapters against source course
        //     $chapters = WebinarChapter::where('webinar_id', $id)->with(['textLessons', 'quizzes'])->get();
        //     if ($chapters->count() > 0) {
        //         $creationTime = time();
        //         $chapterMappings = [];
        //         foreach ($chapters as $chapter) {
        //             $cloneData = $chapter->toArray();
        //             $cloneData['webinar_id'] = $newWebinar->id;
        //             $cloneData['created_at'] = $creationTime;
        //             unset($cloneData['id']);
        //             $clonedChapter = WebinarChapter::Create($cloneData);
        //             $chapterMappings[$chapter->id] = $clonedChapter->id;
        //         }

        //         //Now let's insert the translations
        //         $oldChapterIds = array_keys($chapterMappings);
        //         $chapterTranslations = WebinarChapterTranslation::whereIn('webinar_chapter_id', $oldChapterIds)->get();
        //         if ($chapterTranslations->count() > 0) {
        //             $chapterTranslationsData = [];
        //             foreach ($chapterTranslations as $chapterTranslation) {
        //                 $translation = $chapterTranslation->toArray();
        //                 unset($translation['id']);
        //                 $oldChapterId = $translation['webinar_chapter_id'];
        //                 $translation['webinar_chapter_id'] = $chapterMappings[$oldChapterId];
        //                 $chapterTranslationsData[] = $translation;
        //             }

        //             //bulk insertion of translations
        //             WebinarChapterTranslation::Insert($chapterTranslationsData);
        //         }

        //         //Now that chapters are inserted, let's add text lessons
        //         if (isset($oldChapterIds) && count($oldChapterIds) > 0) {
        //             $textLessons = TextLesson::whereIn('chapter_id', $oldChapterIds)->get();
        //             if ($textLessons->count() > 0) {
        //                 $textLessonMappings = [];
        //                 foreach ($textLessons as $textlesson) {
        //                     $newTextLesson = $textlesson->toArray();
        //                     unset($newTextLesson['id']);
        //                     $newTextLesson['creator_id'] = $user->id;
        //                     $newTextLesson['created_at'] = $creationTime;
        //                     $newTextLesson['updated_at'] = null;
        //                     $newTextLesson['webinar_id'] = $newWebinar->id;
        //                     $newTextLesson['chapter_id'] = $chapterMappings[$textlesson->chapter_id];
        //                     $clonedTextLesson = TextLesson::Create($newTextLesson);
        //                     $textLessonMappings[$textlesson->id] = $clonedTextLesson->id;
        //                 }
        //                 $oldTextLessonIds = array_keys($textLessonMappings);

        //                 //Insert Text Lesson Translations
        //                 $textLessonTranslations = TextLessonTranslation::whereIn('text_lesson_id', $oldTextLessonIds)->get();
        //                 if ($textLessonTranslations->count() > 0) {
        //                     $textLessonsTranslationData = [];
        //                     foreach ($textLessonTranslations as $textLessonTranslation) {
        //                         $translation = $textLessonTranslation->toArray();
        //                         unset($translation['id']); //unset the old id
        //                         $oldTextLessonId = $translation['text_lesson_id'];
        //                         //dd($textLessonMappings);
        //                         $translation['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
        //                         $textLessonsTranslationData[] = $translation;
        //                     }
        //                     //bulk insertion of translations
        //                     TextLessonTranslation::Insert($textLessonsTranslationData);
        //                 }

        //                 //File Attachments
        //                 $files = File::whereIn('chapter_id', $oldChapterIds)->get();
        //                 if ($files->count() > 0) {
        //                     $fileMappings = [];
        //                     foreach ($files as $file) {
        //                         $newFile = $file->toArray();
        //                         unset($newFile['id']);
        //                         $newFile['webinar_id'] = $newWebinar->id;
        //                         $oldChapterId = $newFile['chapter_id'];
        //                         $newFile['chapter_id'] = $chapterMappings[$oldChapterId];
        //                         $insertedFile = File::create($newFile);
        //                         $fileMappings[$file->id] = $insertedFile->id;
        //                     }
        //                     //File Translations
        //                     $oldFileIds = array_keys($fileMappings);
        //                     $oldFileTranslations = FileTranslation::whereIn('file_id', $oldFileIds)->get();
        //                     if ($oldFileTranslations->count() > 0) {
        //                         $fileTranslationsData = [];
        //                         foreach ($oldFileTranslations as $oldFileTranslation) {
        //                             $fileTranslation = $oldFileTranslation->toArray();
        //                             unset($fileTranslation['id']);
        //                             $oldFileId = $fileTranslation['file_id'];
        //                             $fileTranslation['file_id'] = $fileMappings[$oldFileId];
        //                             $fileTranslationsData[] = $fileTranslation;
        //                         }
        //                         FileTranslation::Insert($fileTranslationsData);
        //                     }

        //                     //Text Lesson Attachments
        //                     $lessonAttachments = TextLessonAttachment::whereIn('text_lesson_id', $oldChapterIds)->get();
        //                     if ($lessonAttachments->count() > 0) {
        //                         $textLessonAttachmentData = [];
        //                         foreach ($lessonAttachments as $lessonAttachment) {
        //                             $attachment = $lessonAttachment->toArray();
        //                             unset($attachment['id']);
        //                             $attachment['created_at'] = $creationTime;
        //                             $oldTextLessonId = $attachment['text_lesson_id'];
        //                             $attachment['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
        //                             $oldFileId = $attachment['file_id'];
        //                             $attachment['file_id'] = $fileMappings[$oldFileId];
        //                             $textLessonAttachmentData[] = $attachment;
        //                         }
        //                         TextLessonAttachment::Insert($textLessonAttachmentData);
        //                     }
        //                 }
        //             }
        //         }

        //         //Clone Quizzes
        //         $oldQuizzes = Quiz::whereIn('chapter_id', $oldChapterIds)->get();
        //         if ($oldQuizzes->count() > 0) {
        //             $quizMappings = [];
        //             foreach ($oldQuizzes as $oldQuiz) {
        //                 $quiz = $oldQuiz->toArray();
        //                 unset($quiz['id']);
        //                 $quiz['created_at'] = $creationTime;
        //                 $quiz['updated_at'] = null;
        //                 $oldChapterId = $quiz['chapter_id'];
        //                 $quiz['chapter_id'] = $chapterMappings[$oldChapterId];
        //                 $quiz['webinar_id'] = $newWebinar->id;
        //                 $newQuiz = Quiz::Create($quiz);
        //                 $quizMappings[$oldQuiz->id] = $newQuiz->id;
        //             }
        //             $oldQuizIds = array_keys($quizMappings);

        //             //Quiz Translations
        //             $oldQuizTranslations = QuizTranslation::whereIn('quiz_id', $oldQuizIds)->get();
        //             if ($oldQuizTranslations->count() > 0) {
        //                 $quizTranslationData = [];
        //                 foreach ($oldQuizTranslations as $oldQuizTranslation) {
        //                     $quizTranslation = $oldQuizTranslation->toArray();
        //                     unset($quizTranslation['id']);
        //                     $oldQuizId = $quizTranslation['quiz_id'];
        //                     $quizTranslation['quiz_id'] = $quizMappings[$oldQuizId];
        //                     $quizTranslationData[] = $quizTranslation;
        //                 }
        //                 QuizTranslation::Insert($quizTranslationData);
        //             }

        //             //Quiz Questions
        //             $oldQuizQuestions = QuizzesQuestion::whereIn('quiz_id', $oldQuizIds)->get();
        //             if ($oldQuizQuestions->count() > 0) {
        //                 $quizQuestionMappings = [];
        //                 foreach ($oldQuizQuestions as $oldQuizQuestion) {
        //                     $quizQuestion = $oldQuizQuestion->toArray();
        //                     unset($quizQuestion['id']);
        //                     $oldQuizId = $quizQuestion['quiz_id'];
        //                     $quizQuestion['quiz_id'] = $quizMappings[$oldQuizId];
        //                     $quizQuestion['created_at'] = $creationTime;
        //                     $quizQuestion['updated_at'] = null;
        //                     $newQuizQuestion = QuizzesQuestion::Create($quizQuestion);
        //                     $quizQuestionMappings[$oldQuizQuestion->id] = $newQuizQuestion->id;
        //                 }

        //                 //Quiz Question Translations
        //                 $oldQuizQuestionIds = array_keys($quizQuestionMappings);
        //                 $oldQuizQuestionTranslations = QuizzesQuestionTranslation::whereIn('quizzes_question_id', $oldQuizQuestionIds)->get();
        //                 if ($oldQuizQuestionTranslations->count() > 0) {
        //                     $questionTranslationsData = [];
        //                     foreach ($oldQuizQuestionTranslations as $oldQuizQuestionTranslation) {
        //                         $questionTranslation = $oldQuizQuestionTranslation->toArray();
        //                         unset($questionTranslation['id']);
        //                         $oldQuizQuestionId = $questionTranslation['quizzes_question_id'];
        //                         $questionTranslation['quizzes_question_id'] = $quizQuestionMappings[$oldQuizQuestionId];
        //                         $questionTranslationsData[] = $questionTranslation;
        //                     }
        //                     QuizzesQuestionTranslation::Insert($questionTranslationsData);
        //                 }

        //                 //Quiz Question Answers
        //                 $oldQuestionAnswers = QuizzesQuestionsAnswer::whereIn('question_id', $oldQuizQuestionIds)->get();
        //                 if ($oldQuestionAnswers->count() > 0) {
        //                     $questionAnswerMappings = [];
        //                     foreach ($oldQuestionAnswers as $oldQuestionAnswer) {
        //                         $questionAnswer = $oldQuestionAnswer->toArray();
        //                         unset($questionAnswer['id']);
        //                         $oldQuestionId = $questionAnswer['question_id'];
        //                         $questionAnswer['question_id'] = $quizQuestionMappings[$oldQuestionId];
        //                         $questionAnswer['created_at'] = $creationTime;
        //                         $quizQuestionAnswer = QuizzesQuestionsAnswer::Create($questionAnswer);
        //                         $questionAnswerMappings[$oldQuestionAnswer->question_id] = $quizQuestionAnswer->id;
        //                     }

        //                     //Quiz Question Answer Translations
        //                     $oldQuestionAnswerIds = array_keys($questionAnswerMappings);
        //                     $oldAnswerTranslations = QuizzesQuestionsAnswerTranslation::whereIn('quizzes_questions_answer_id', $oldQuestionAnswerIds)->get();
        //                     if ($oldAnswerTranslations->count() > 0) {
        //                         $answerTranslationsData = [];
        //                         foreach ($oldAnswerTranslations as $oldAnswerTranslation) {
        //                             $answerTranslation = $oldAnswerTranslation->toArray();
        //                             unset($answerTranslation['id']);
        //                             $oldQuestionAnswerId = $answerTranslation['quizzes_questions_answer_id'];
        //                             $answerTranslation['quizzes_questions_answer_id'] = $questionAnswerMappings[$oldQuestionAnswerId];
        //                             $answerTranslationsData[] = $answerTranslation;
        //                         }
        //                         QuizzesQuestionsAnswerTranslation::Insert($answerTranslationsData);
        //                     }
        //                 }
        //             }
        //         }
        //     }

        //     if (isset($newWebinar->id)) {
        //         //Audit Trail entry - course duplicated
        //         $audit = new AuditTrail();
        //         $audit->user_id = $user->id;
        //         $audit->organ_id = $user->organ_id;
        //         $audit->role_name = $user->role_name;
        //         $audit->audit_type = AuditTrail::auditType['course_duplicated'];
        //         $audit->added_by = $user->id;
        //         $audit->description = "User {$user->full_name} ({$user->id}) cloned existing course ({$id}). New Course id: {$newWebinar->id}";
        //         $ip = null;
        //         $ip = getClientIp();
        //         $audit->ip = ip2long($ip);
        //         $audit->save();
        //     }

        //     // return redirect('/panel/webinars/' . $newWebinar->id . '/edit');
        //     $webinarEditRoute = route('admin.webinar_edit', ['id' => $newWebinar->id]);
        //     return redirect($webinarEditRoute);
        // }
    // }

    public function duplicate($id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            // Retrieve webinar with content
            $originalWebinar = Webinar::with('chapters.chapterItems')->findOrFail($id);
            // Check if webinar exists
            if (!$originalWebinar) {

                abort(404, 'Webinar is not found');
            }

            // webinar quizzes without chapter
            $quizzesWithoutChapter = $originalWebinar->quizzes->whereNull('chapter_id');

            // Duplicate webinar

            $duplicateWebinarData = $originalWebinar->toArray();
            $duplicateWebinarData['title'] = $originalWebinar->title . ' ' . trans('public.copy');
            $duplicateWebinarData['slug'] = Webinar::makeSlug($duplicateWebinarData['title']);
            $duplicateWebinarData['created_at'] = time();
            $duplicateWebinarData['updated_at'] = null;
            $duplicateWebinarData['creator_id'] = $user->id;
            $duplicateWebinarData['status'] = Webinar::$pending;

            $newWebinar = Webinar::create($duplicateWebinarData);

            // Create or update webinar translation
            $webinarTranslationData = [
                'title' => $duplicateWebinarData['title'],
                'description' => $originalWebinar->description,
                'seo_description' => $originalWebinar->seo_description,
                'locale' => mb_strtolower($originalWebinar->locale),
            ];
            $webinarTranslation = WebinarTranslation::updateOrCreate(['webinar_id' => $newWebinar->id], $webinarTranslationData);

            if ($originalWebinar->chapters && count($originalWebinar->chapters) > 0) {
                foreach ($originalWebinar->chapters as $chapter) {
                    $chapterDuplication = array_merge($chapter->toArray(),
                    [
                        'user_id' => $user->id,
                        'webinar_id' => $newWebinar->id,
                        'created_at' => time()
                    ]);
                    // following query will generate create chapter as well as chapter translation record
                    $duplicatedChapter = WebinarChapter::create($chapterDuplication);
                    if ($chapter->chapterItems && count($chapter->chapterItems) > 0) {
                        foreach ($chapter->chapterItems as $item) {

                            $model = ($item->type == 'text_lesson')
                                ? TextLesson::class
                                : (($item->type == 'file')
                                    ? File::class
                                    : (($item->type == 'quiz')
                                        ? Quiz::class
                                        : (($item->type == 'session')
                                            ? Session::class
                                            : WebinarAssignment::class)));

                            $duplicatedData = ($item->type == 'text_lesson')
                                ? $item->textLesson
                                : (($item->type == 'file')
                                    ? $item->file
                                    : (($item->type == 'quiz')
                                        ? $item->quiz
                                        : (($item->type == 'session')
                                            ? $item->session
                                            : $item->assignment)));


                            if ($item->type == 'text_lesson') {

                                $duplicationData = $duplicatedData->toArray();
                                $duplicationData['webinar_id']  = $newWebinar->id;
                                $duplicationData['chapter_id'] = $duplicatedChapter->id;
                                $duplicationData['creator_id']  = $user->id;
                                $duplicationData['created_at'] = time();
                                $duplicationData['updated_at'] = Null;

                                $duplicatedlesson = $model::create($duplicationData); //creating duplication in text_lessons table

                                if ($duplicatedlesson) {

                                    $duplicateItemData = [
                                        'user_id' => $user->id,
                                        'chapter_id' => $duplicatedChapter->id,
                                        'item_id' => $duplicatedlesson->id,
                                        'type' => $item->type,
                                        'order' => $item->order,
                                        'created_at' => time()
                                    ];

                                    $duplicatedItemLesson = WebinarChapterItem::create($duplicateItemData);
                                    // for textLesson quizzes
                                    if ($duplicatedItemLesson && ($duplicatedData->quizzes && count($duplicatedData->quizzes) > 0)) {

                                        foreach ($duplicatedData->quizzes as $quiz) {
                                            $quizAsChapItem = WebinarChapterItem::where('item_id',$quiz->id)->where('chapter_id', $chapter->id)->where('type', 'quiz')->first();
                                            if ($quizAsChapItem)  {

                                                $newQuizData = array_merge($quiz->toArray(), [
                                                    'webinar_id' => $newWebinar->id,
                                                    'creator_id' => $user->id,
                                                    'chapter_id' => $duplicatedChapter->id,
                                                    'text_lesson_id' => $duplicatedlesson->id,
                                                    'created_at' => time(),
                                                    'updated_at' => Null
                                                ]);
                                                $duplicatedLessonQuiz = Quiz::create($newQuizData);
    
                                                if ($duplicatedLessonQuiz) {
    
                                                    $duplicatedQuizItemData = [
                                                        'user_id' => $user->id,
                                                        'chapter_id' =>$duplicatedChapter->id,
                                                        'item_id' => $duplicatedLessonQuiz->id,
                                                        'type' => 'quiz',
                                                        'order' =>  $quizAsChapItem->order,
                                                        'created_at' => time()
                                                    ];
                                                    $duplicatedItemQuiz = WebinarChapterItem::create($duplicatedQuizItemData);
    
                                                    if (($duplicatedLessonQuiz && $duplicatedItemQuiz) && ($quiz->quizQuestions && count($quiz->quizQuestions))) {
    
                                                        foreach ($quiz->quizQuestions as $question) {
    
                                                            $duplicatedQuizQuestionsData = array_merge($question->toArray(),
                                                            [
                                                                'quiz_id' => $duplicatedLessonQuiz->id,
                                                                'creator_id' => $user->id,
                                                                'created_at' => time(),
                                                                'updated_at' => Null
                                                            ]);
    
                                                            $duplicatedQuizQuestion = QuizzesQuestion::create($duplicatedQuizQuestionsData);
    
                                                            if ($duplicatedQuizQuestion) {
    
                                                                if ($question->quizzesQuestionsAnswers && count($question->quizzesQuestionsAnswers) > 0) {
    
                                                                    foreach($question->quizzesQuestionsAnswers as $answer) {
                                                                        $duplicateAnswerData = array_merge($answer->toArray(),
                                                                        [
                                                                            'creator_id' => $user->id,
                                                                            'question_id' => $duplicatedQuizQuestion->id,
                                                                            'created_at' => time(),
                                                                            'updated_at' => Null
                                                                        ]);
    
                                                                        $duplicatedQuizzeQustionAnswer = QuizzesQuestionsAnswer::create($duplicateAnswerData);
    
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                        }
                                    }

                                    // for textLesson attachments
                                    if ($duplicatedData->attachments && count($duplicatedData->attachments) > 0) {

                                        foreach($duplicatedData->attachments as $attachment) {

                                            $duplicatedFile = File::create(array_merge($attachment->file->toArray(),[
                                                'creator_id' => $user->id,
                                                'webinar_id' => $newWebinar->id,
                                                'chapter_id' => $duplicatedChapter->id,
                                                'created_at' => time()
                                            ]));

                                            if ($duplicatedFile) {
                                                TextLessonAttachment::create(
                                                [
                                                    'text_lesson_id' => $duplicatedlesson->id,
                                                    'file_id' => $duplicatedFile->id,
                                                    'created_at' => time()
                                                ]);

                                                // for making chapterItems
                                                $fileAsChapItem = WebinarChapterItem::where('item_id',$attachment->file->id)->where('chapter_id', $chapter->id)->where('type', 'file')->first();
                                                $attachmentItemOrderCounter = 0;
                                                WebinarChapterItem::create([
                                                    'user_id' => $user->id,
                                                    'chapter_id' =>$duplicatedChapter->id,
                                                    'item_id' => $duplicatedFile->id,
                                                    'type' => 'file',
                                                    'order' =>  $fileAsChapItem->order ?? $attachmentItemOrderCounter+1,
                                                    'created_at' => time()
                                                ]);
                                            }
                                        }
                                    }
                                }
                            } elseif (($item->type == 'quiz' && $item->quiz) && !$item->quiz->text_lesson_id) {
                                // $quizAsChapItem = WebinarChapterItem::where('item_id',$item->quiz->id)->where('chapter_id', $chapter->id)->where('type', 'quiz')->first();
                                $newQuizData = array_merge($item->quiz->toArray(), [
                                    'webinar_id' => $newWebinar->id,
                                    'creator_id' => $user->id,
                                    'chapter_id' => $duplicatedChapter->id,
                                    'text_lesson_id' => Null,
                                    'created_at' => time(),
                                    'updated_at' => Null
                                ]);
                                $duplicatedQuiz = Quiz::create($newQuizData);

                                if ($duplicatedQuiz) {
                                    $duplicatedQuizItemData = [
                                        'user_id' => $user->id,
                                        'chapter_id' =>$duplicatedChapter->id,
                                        'item_id' => $duplicatedQuiz->id,
                                        'type' => 'quiz',
                                        'order' =>  $item->order,
                                        'created_at' => time()
                                    ];
                                    $duplicatedItemQuiz = WebinarChapterItem::create($duplicatedQuizItemData);

                                    if ($duplicatedQuiz && ($item->quiz->quizQuestions && count($item->quiz->quizQuestions))) {

                                        foreach ($item->quiz->quizQuestions as $question) {

                                            $duplicatedQuizQuestionsData = array_merge($question->toArray(),
                                            [
                                                'quiz_id' => $duplicatedQuiz->id,
                                                'creator_id' => $user->id,
                                                'created_at' => time(),
                                                'updated_at' => Null
                                            ]);

                                            $duplicatedQuizQuestion = QuizzesQuestion::create($duplicatedQuizQuestionsData);

                                            if ($duplicatedQuizQuestion) {

                                                if ($question->quizzesQuestionsAnswers && count($question->quizzesQuestionsAnswers) > 0) {

                                                    foreach($question->quizzesQuestionsAnswers as $answer) {
                                                        $duplicateAnswerData = array_merge($answer->toArray(),
                                                        [
                                                            'creator_id' => $user->id,
                                                            'question_id' => $duplicatedQuizQuestion->id,
                                                            'created_at' => time(),
                                                            'updated_at' => Null
                                                        ]);

                                                        $duplicatedQuizzeQustionAnswer = QuizzesQuestionsAnswer::create($duplicateAnswerData);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($item->type == 'file') {
                                // checking is the file attached or not with text lesson
                                $isFileAttached = TextLessonAttachment::find($item->item_id);
                                if (!$isFileAttached) {

                                    $duplicatedFile = File::create(array_merge($item->file->toArray(),[
                                        'creator_id' => $user->id,
                                        'webinar_id' => $newWebinar->id,
                                        'chapter_id' => $duplicatedChapter->id,
                                        'created_at' => time()
                                    ]));

                                    if ($duplicatedFile) {
                                        // for making chapterItems
                                        // $fileAsChapItem = WebinarChapterItem::where('item_id',$item->file->id)->where('chapter_id', $chapter->id)->where('type', 'file')->first();
                                        WebinarChapterItem::create([
                                            'user_id' => $user->id,
                                            'chapter_id' =>$duplicatedChapter->id,
                                            'item_id' => $duplicatedFile->id,
                                            'type' => 'file',
                                            'order' =>  $item->order,
                                            'created_at' => time()
                                        ]);
                                    }
                                }
                            }

                        }
                    }

                }
            } elseif ($quizzesWithoutChapter && count($quizzesWithoutChapter) > 0) {

                // quizzes without chapters
                foreach ($quizzesWithoutChapter as $quiz) {

                    $newQuizData = array_merge($quiz->toArray(), [
                        'webinar_id' => $newWebinar->id,
                        'creator_id' => $user->id,
                        'created_at' => time(),
                        'updated_at' => Null
                    ]);
                    $duplicatedQuiz = Quiz::create($newQuizData);

                    if ($duplicatedQuiz && ($quiz->quizQuestions && count($quiz->quizQuestions))) {

                        foreach ($quiz->quizQuestions as $question) {

                            $duplicatedQuizQuestionsData = array_merge($question->toArray(),
                            [
                                'quiz_id' => $duplicatedQuiz->id,
                                'creator_id' => $user->id,
                                'created_at' => time(),
                                'updated_at' => Null
                            ]);

                            $duplicatedQuizQuestion = QuizzesQuestion::create($duplicatedQuizQuestionsData);

                            if ($duplicatedQuizQuestion) {

                                if ($question->quizzesQuestionsAnswers && count($question->quizzesQuestionsAnswers) > 0) {

                                    foreach($question->quizzesQuestionsAnswers as $answer) {
                                        $duplicateAnswerData = array_merge($answer->toArray(),
                                        [
                                            'creator_id' => $user->id,
                                            'question_id' => $duplicatedQuizQuestion->id,
                                            'created_at' => time(),
                                            'updated_at' => Null
                                        ]);

                                        $duplicatedQuizzeQustionAnswer = QuizzesQuestionsAnswer::create($duplicateAnswerData);
                                    }
                                }
                            }
                        }
                    }
                    
                }
            }
            // creating record in audit_trials table
            $audit = new AuditTrail();
            $audit->user_id = $user->id;
            $audit->organ_id = $user->organ_id;
            $audit->role_name = $user->role_name;
            $audit->audit_type = AuditTrail::auditType['course_duplicated'];
            $audit->added_by = $user->id;
            $audit->description = "User {$user->full_name} ({$user->id}) cloned existing course ({$id}). New Course id: {$newWebinar->id}";
            $ip = getClientIp();
            $audit->ip = ip2long($ip);
            $audit->save();

            $webinarEditRoute = route('admin.webinar_edit', ['id' => $newWebinar->id]);
            return redirect($webinarEditRoute);

        } catch (Exception $e) {
            // Log and handle exceptions
            Log::error('Error in creating duplicate webinar content: ' . $e->getMessage());
            abort(404, 'Webinar content is not duplicated');
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');
        $data = $request->except('ajax', 'quiz_id');
        $webinar = Webinar::find($id);

        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $reject = (!empty($data['draft']) and $data['draft'] == 'reject');
        $publish = (!empty($data['draft']) and $data['draft'] == 'publish');

        $rules = [
            'webinar_type' => 'required|in:webinar,course,text_lesson',
            'web_title' => 'required|max:255',
            'slug' => 'max:255|unique:webinars,slug,' . $webinar->id,
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required',
        ];

        if ($webinar->isWebinar()) {
            $rules['start_date'] = 'required|date';
            $rules['duration'] = 'required';
            $rules['capacity'] = 'required|integer';
        }
        $this->validate($request, $rules);
        if (!empty($data['teacher_id'])) {
            $teacher = User::find($data['teacher_id']);
            $creator = $webinar->creator;

            if (empty($teacher) or ($creator->isOrganization() and ($teacher->organ_id != $creator->id and $teacher->id != $creator->id))) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('admin/main.is_not_the_teacher_of_this_organization'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        }


        if (empty($data['slug'])) {
            $data['slug'] = Webinar::makeSlug($data['web_title']);
        }

        $data['status'] = $publish ? Webinar::$active : ($reject ? Webinar::$inactive : ($isDraft ? Webinar::$isDraft : Webinar::$pending));
        $data['updated_at'] = time();

        if (!empty($data['start_date']) and $webinar->type == 'webinar') {
            if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                $data['timezone'] = getTimezone();
            }

            $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

            $data['start_date'] = $startDate->getTimestamp();
        } else {
            $data['start_date'] = null;
        }


        $data['support'] = !empty($data['support']) ? true : false;
        $data['certificate'] = !empty($data['certificate']) ? true : false;
        $data['downloadable'] = !empty($data['downloadable']) ? true : false;
        $data['partner_instructor'] = !empty($data['partner_instructor']) ? true : false;
        $data['subscribe'] = !empty($data['subscribe']) ? true : false;
        $data['forum'] = !empty($data['forum']) ? true : false;
        $data['private'] = !empty($data['private']) ? true : false;
        $data['enable_waitlist'] = (!empty($data['enable_waitlist']));

        if (empty($data['partner_instructor'])) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
            unset($data['partners']);
        }

        if ($data['category_id'] !== $webinar->category_id) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            foreach ($filters as $filter) {
                WebinarFilterOption::create([
                    'webinar_id' => $webinar->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('webinar_id', $webinar->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'webinar_id' => $webinar->id,
                    'title' => $tag,
                ]);
            }
        }

        if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

            foreach ($request->get('partners') as $partnerId) {
                WebinarPartnerTeacher::create([
                    'webinar_id' => $webinar->id,
                    'teacher_id' => $partnerId,
                ]);
            }
        }
        unset(
            $data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters']
        );

        if (empty($data['video_demo'])) {
            unset($data['video_demo_source']);
        }

        if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
            $data['video_demo_source'] = 'upload';
        }

        $newCreatorId = !empty($data['organ_id']) ? $data['organ_id'] : $data['teacher_id'];
        $changedCreator = ($webinar->creator_id != (int)$newCreatorId);

        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

        $webinar->update([
            'slug' => $data['slug'],
            'creator_id' => (int)$newCreatorId,
            'teacher_id' => $data['teacher_id'],
            'type' => $data['webinar_type'],
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'capacity' => $data['capacity'] ?? null,
            'start_date' => $data['start_date'],
            'timezone' => $data['timezone'] ?? null,
            'duration' => $data['duration'] ?? null,
            'support' => $data['support'],
            'certificate' => $data['certificate'],
            'private' => $data['private'],
            'enable_waitlist' => $data['enable_waitlist'],
            'downloadable' => $data['downloadable'],
            'partner_instructor' => $data['partner_instructor'],
            'subscribe' => $data['subscribe'],
            'forum' => $data['forum'],
            'access_days' => $data['access_days'] ?? null,
            'price' => $data['price'],
            'organization_price' => $data['organization_price'] ?? null,
            'category_id' => $data['category_id'],
            'points' => $data['points'] ?? null,
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'],
            'updated_at' => time(),
        ]);

        if ($data['video_demo'] !== null) {
            $webinar->update([
                'video_demo_source' => $data['video_demo_source'],
            ]);
        }

        if ($webinar) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['web_title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        if ($webinar && !$request->has("visible_to_all") && $request->has("organizations")) {
            CourseVisibility::where("course_id", $webinar->id)->delete();
            $organizations = $request->organizations;
            foreach ($organizations as $organizationId) {
                CourseVisibility::create([
                    "course_id" => $webinar->id,
                    "organization_id" => $organizationId,
                ]);
            }
        } else {
            CourseVisibility::where("course_id", $webinar->id)->delete();
        }

        if ($publish) {
            sendNotification('course_approve', ['[c.title]' => $webinar->title], $webinar->teacher_id);
            $createClassesReward = RewardAccounting::calculateScore(Reward::CREATE_CLASSES);
            RewardAccounting::makeRewardAccounting(
                $webinar->creator_id,
                $createClassesReward,
                Reward::CREATE_CLASSES,
                $webinar->id,
                true
            );
        } elseif ($reject) {
            sendNotification('course_reject', ['[c.title]' => $webinar->title], $webinar->teacher_id);
        }

        if ($changedCreator) {
            $this->webinarChangedCreator($webinar);
        }

        removeContentLocale();

        return back();
    }
    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_delete');

        Webinar::find($id)->delete();
        return redirect()->back();
        // return redirect('/admin/webinars?type=text_lesson');
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $webinar = Webinar::select('id')
            ->whereTranslationLike('title', "%$term%")
            ->get();

        return response()->json($webinar, 200);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_webinars_export_excel');

        $query = Webinar::query();

        $query = $this->filterWebinar($query, $request)
            ->with(['teacher' => function ($qu) {
                $qu->select('id', 'full_name');
            }, 'sales']);

        $webinars = $query->get();

        $webinarExport = new WebinarsExport($webinars);

        return Excel::download($webinarExport, 'webinars.xlsx');
    }

    //  public function processEnrolment($slug, $payment_type, $id)
    // {
    //     $authUser = auth()->user();

    //     $user = User::where('id', $id)->first();

    //     if(
    //         empty($user) || $user->role_name !== Role::$user
    //         || empty($user->organ_id)
    //     ) {
    //         $toastData = [
    //             'title' => trans('panel.invalid_user'),
    //             'msg' => trans('panel.invalid_or_missing_user_reference'),
    //             'status' => 'error'
    //         ];
    //         return back()->with(['toast' => $toastData]);
    //     }

    //     $course = Webinar::where('slug', $slug)
    //             ->where('status', 'active')
    //             ->first();
    //     if (!in_array($payment_type, ['free', 'paid'])) {
    //         $toastData = [
    //             'title' => trans('panel.invalid_request'),
    //             'msg' => trans('panel.invalid_course_payment_type'),
    //             'status' => 'error'
    //         ];
    //         return back()->with(['toast' => $toastData]);
    //     }
    //     if (!empty($course)) {
    //         if (strtolower($payment_type) === 'free') {

    //             if (!empty($course->price) and $course->price > 0) {
    //                 $toastData = [
    //                     'title' => trans('cart.fail_purchase'),
    //                     'msg' => trans('cart.course_not_free'),
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //             }

    //             $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
    //             if (isset($alreadyPurchased->id)) {
    //                 $toastData = [
    //                     'title' => trans('cart.fail_purchase'),
    //                     'msg' => 'Course already purchased',
    //                     'status' => 'error'
    //                 ];
    //                 return back()->with(['toast' => $toastData]);
    //             }

    //             Sale::create([
    //                 'buyer_id' => $id,
    //                 'seller_id' => $course->creator_id,
    //                 'webinar_id' => $course->id,
    //                 'type' => Sale::$webinar,
    //                 'payment_method' => Sale::$credit,
    //                 'amount' => 0,
    //                 'total_amount' => 0,
    //                 'created_at' => time(),
    //             ]);

    //             $toastData = [
    //                 'title' => '',
    //                 'msg' => trans('cart.success_pay_msg_for_free_course'),
    //                 'status' => 'success'
    //             ];

    //             if ( env('APP_ENV') == 'production' ) {
    //                 $title = $course->slug;
    //                 $message = trans('cart.success_pay_msg_for_free_course');
    //                 Mail::to( $user->email )
    //                     ->send(new SendNotifications(['title' => $title, 'message' => $message]));
    //                 Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
    //             }

    //             return back()->with(['toast' => $toastData]);

    //         } else {
    //             if (!empty($course->price) and $course->price > 0) {

    //                 $organizationId = $user->organ_id;

    //                 $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
    //                 if (isset($alreadyPurchased->id)) {
    //                     $toastData = [
    //                         'title' => trans('cart.fail_purchase'),
    //                         'msg' => 'Course already purchased',
    //                         'status' => 'error'
    //                     ];
    //                     return back()->with(['toast' => $toastData]);
    //                 }

    //                 //1. Enter sale against organization
    //                 //create sale against organization
    //                 $price = $course->price;
    //                 $financialSettings = getFinancialSettings();
    //                 $taxPrice = 0;
    //                 if (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0 and $price > 0) {
    //                     $tax = $financialSettings['tax'];
    //                     $taxPrice = $price * $tax / 100;
    //                 }
    //                 $totalAmount = $price + $taxPrice;

    //                 $saleTime = time();
    //                 $organizationSale = Sale::create([
    //                     'buyer_id' => $organizationId,
    //                     'seller_id' => $course->creator_id,
    //                     'webinar_id' => $course->id,
    //                     'type' => Sale::$webinar,
    //                     'payment_method' => Sale::$credit,
    //                     'amount' => $price,
    //                     'tax' => $taxPrice,
    //                     'total_amount' => $totalAmount,
    //                     'payment_status' => Sale::$paymentStatus['pending'],
    //                     'created_at' => $saleTime,
    //                     'paid_for' => $id,
    //                 ]);


    //                 //2. Enter sale against student
    //                 if (!empty($organizationSale->id)) {
    //                     //$auditedUser = User::find($break->user_id);
    //                     $ip = null;
    //                     $ip = getClientIp();
    //                     $ipLong = ip2long($ip);

    //                     $organizationUser = User::where('id', $organizationId)->first();
    //                     $auditMessage = "User {$authUser->full_name} ({$authUser->id}) enrolled student ({$user->full_name} [{$user->id}]) in course {$course->title} ({$course->id}) on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
    //                     $audit = new AuditTrail();
    //                     $audit->user_id = $authUser->id;
    //                     $audit->organ_id = $organizationId;
    //                     $audit->role_name = $authUser->role_name;
    //                     $audit->audit_type = AuditTrail::auditType['paid_course_purchase'];
    //                     $audit->added_by = null;
    //                     $audit->description = $auditMessage;
    //                     $audit->ip = $ipLong;
    //                     $audit->save();

    //                     $studentSale = Sale::create([
    //                         'buyer_id' => $id,
    //                         'seller_id' => $course->creator_id,
    //                         'webinar_id' => $course->id,
    //                         'type' => Sale::$webinar,
    //                         'payment_method' => Sale::$credit,
    //                         'amount' => $price,
    //                         'tax' => $taxPrice,
    //                         'discount' =>   $totalAmount,
    //                         'total_amount' => 0,
    //                         'created_at' => $saleTime,
    //                         'sale_reference_id' => $organizationSale->id,
    //                         'self_payed' => 0,
    //                     ]);

    //                     if (!empty($studentSale->id)) {

    //                         $auditMessage = "User {$user->full_name} ({$user->id}) enrolled in course {$course->title} ({$course->id}) by {$authUser->full_name} ({$authUser->id}), on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
    //                         $audit = new AuditTrail();
    //                         $audit->user_id = $user->id;
    //                         $audit->organ_id = $organizationId;
    //                         $audit->role_name = $user->role_name;
    //                         $audit->audit_type = AuditTrail::auditType['course_enrolment'];
    //                         $audit->added_by = $authUser->id;
    //                         $audit->description = $auditMessage;
    //                         $audit->ip = $ipLong;
    //                         $audit->save();

    //                         $toastData = [
    //                             'title' => '',
    //                             'msg' => trans('cart.success_pay_msg_for_course'),
    //                             'status' => 'success'
    //                         ];

    //                         if ( env('APP_ENV') == 'production' ) {
    //                             $title = $course->slug;
    //                             $message = trans('cart.success_pay_msg_for_course');
    //                             Mail::to( $user->email )
    //                                 ->send(new SendNotifications(['title' => $title, 'message' => $message]));
    //                             Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
    //                         }

    //                         return back()->with(['toast' => $toastData]);
    //                     }

    //                 }
    //             }

    //             $toastData = [
    //                 'title' => trans('cart.fail_purchase'),
    //                 'msg' => trans('cart.course_not_paid'),
    //                 'status' => 'error'
    //             ];
    //             return back()->with(['toast' => $toastData]);
    //         }
    //     }
    //     abort(404);
    // }

    /* public function free(Request $request, $slug, $id)
    {
        $user = User::where('id', $id)->first();
        // $user = auth()->user();

        $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

        if (!empty($course)) {
                // $checkCourseForSale = checkCourseForSale($course, $user);

                // if ($checkCourseForSale != 'ok') {
                //     return $checkCourseForSale;
                // }

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
                if (isset($alreadyPurchased->id)) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => 'Course already purchased',
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $id,
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
                return back()->with(['toast' => $toastData]);
            }
            abort(404);
    } */

    public function getUserLanguagesLists()
    {
        $generalSettings = getGeneralSettings();
        $userLanguages = $generalSettings ? $generalSettings['user_languages'] : null;

        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        if (count($userLanguages) > 0) {
            foreach ($userLanguages as $locale => $language) {
                if (mb_strtolower($locale) == mb_strtolower(app()->getLocale())) {
                    $firstKey = array_key_first($userLanguages);

                    if ($firstKey != $locale) {
                        $firstValue = $userLanguages[$firstKey];

                        unset($userLanguages[$locale]);
                        unset($userLanguages[$firstKey]);

                        $userLanguages = array_merge([
                            $locale => $language,
                            $firstKey => $firstValue
                        ], $userLanguages);
                    }
                }
            }
        }

        return $userLanguages;
    }

    //change order of chapter items of webinar in webinar_chapter_items table
    public function orderItems(Request $request)
    {
        $this->authorize('admin_webinars_edit');
        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tableName = $data['table'];
        $itemIds = explode(',', $data['items']);

        if (!is_array($itemIds) and !empty($itemIds)) {
            $itemIds = [$itemIds];
        }

        if (!empty($itemIds) and is_array($itemIds) and count($itemIds)) {
            switch ($tableName) {
                case 'tickets':
                    foreach ($itemIds as $order => $id) {
                        Ticket::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'sessions':
                    foreach ($itemIds as $order => $id) {
                        Session::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'files':
                    foreach ($itemIds as $order => $id) {
                        File::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'text_lessons':
                    foreach ($itemIds as $order => $id) {
                        TextLesson::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapters':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapter::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapter_items':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapterItem::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                case 'bundle_webinars':
                    foreach ($itemIds as $order => $id) {
                        BundleWebinar::where('id', $id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
            }
        }

        return response()->json([
            'title' => trans('public.request_success'),
            'msg' => trans('update.items_sorted_successful')
        ]);
    }
}
