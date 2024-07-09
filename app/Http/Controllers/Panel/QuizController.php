<?php

namespace App\Http\Controllers\Panel;

use App\User;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Webinar;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\QuizzesResult;
use App\Helpers\WebinarHelper;
use App\Models\WebinarChapter;
use App\Models\QuizzesQuestion;
use App\Traits\QuizQuestionTrait;
use App\Models\WebinarChapterItem;
use App\Http\Controllers\Controller;
use App\Models\WebinarPartnerTeacher;
use App\Models\QuizzesQuestionsAnswer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Translation\QuizTranslation;

class QuizController extends Controller
{
    use QuizQuestionTrait;

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Quiz::where('creator_id', $user->id);

        //following line will get the all quizzes of a creator
        $allQuizzesLists =$query->get(['id','webinar_id']);

        $quizzesCount = deepClone($query)->count();

        $quizFilters = $this->filters($request, $query);

        $quizzes = $quizFilters->with([
            'webinar',
            'quizQuestions',
            'quizResults',
        ])->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $userSuccessRate = [];
        $questionsCount = 0;
        $userCount = 0;

        foreach ($quizzes as $quiz) {

            $countSuccess = $quiz->quizResults
                ->where('status', \App\Models\QuizzesResult::$passed)
                ->pluck('user_id')
                ->count();

            $rate = 0;
            if ($countSuccess) {
                $rate = round($countSuccess / $quiz->quizResults->count() * 100);
            }

            $quiz->userSuccessRate = $rate;

            $questionsCount += $quiz->quizQuestions->count();
            $userCount += $quiz->quizResults
                ->pluck('user_id')
                ->count();
        }

        $data = [
            'pageTitle' => trans('quiz.quizzes_list_page_title'),
            'quizzes' => $quizzes,
            'userSuccessRate' => $userSuccessRate,
            'questionsCount' => $questionsCount,
            'quizzesCount' => $quizzesCount,
            'userCount' => $userCount,
            'allQuizzesLists' => $allQuizzesLists
        ];
        return view(getTemplate() . '.panel.quizzes.lists', $data);
    }

    public function filters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $quiz_id = $request->get('quiz_id');
        $total_mark = $request->get('total_mark');
        $status = $request->get('status');
        $active_quizzes = $request->get('active_quizzes');


        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($quiz_id) and $quiz_id != 'all') {
            $query->where('id', $quiz_id);
        }

        if ($status and $status !== 'all') {
            $query->where('status', strtolower($status));
        }

        if (!empty($active_quizzes)) {
            $query->where('status', 'active');
        }

        if ($total_mark) {
            $query->where('total_mark', '>=', $total_mark);
        }

        return $query;
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $webinars = Webinar::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhere('creator_id', $user->id);
        })->get();

        $locale = $request->get('locale', app()->getLocale());

        $webinarController = new WebinarController();

        $data = [
            'pageTitle' => trans('quiz.new_quiz_page_title'),
            'webinars' => $webinars,
            'userLanguages' => $webinarController->getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];

        return view(getTemplate() . '.panel.quizzes.create', $data);
    }

    // public function store(Request $request)
    // {
    //     $data = $request->get('ajax');
    //     $rules = [
    //         'title' => 'required|max:255',
    //         'webinar_id' => 'nullable',
    //         'pass_mark' => 'required',
    //     ];

    //     if ($request->ajax()) {
    //         $validate = Validator::make($data, $rules);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'code' => 422,
    //                 'errors' => $validate->errors()
    //             ], 422);
    //         }
    //     } else {
    //         $this->validate($request, $rules);
    //     }

    //     $user = auth()->user();

    //     $webinar = null;
    //     $chapter = null;
    //     $lesson = null;
    //     if (!empty($data['webinar_id'])) {
    //         $webinar = Webinar::where('id', $data['webinar_id'])
    //             ->where(function ($query) use ($user) {
    //                 $query->where('teacher_id', $user->id)
    //                     ->orWhere('creator_id', $user->id);
    //             })->first();

    //         if (!empty($webinar) and !empty($data['chapter_id'])) {
    //             $chapter = WebinarChapter::where('id', $data['chapter_id'])
    //                 ->where('webinar_id', $webinar->id)
    //                 ->first();
    //         }
    //         if (!empty($chapter) and !empty($data['lesson_id'])) {
    //             $lesson = TextLesson::where('id', $data['lesson_id'])
    //                 ->where('chapter_id', $chapter->id)
    //                 ->first();
    //         }
    //     }

    //     $quiz = Quiz::create([
    //         'webinar_id' => !empty($webinar) ? $webinar->id : null,
    //         'chapter_id' => !empty($chapter) ? $chapter->id : null,
    //         'text_lesson_id' => !empty($lesson) ? $lesson->id : null,
    //         'creator_id' => $user->id,
    //         'drip_feed' => $request->drip_feed,
    //         'show_after_days' => $request->show_after_days ?? null,
    //         'webinar_title' => !empty($webinar) ? $webinar->title : null,
    //         'attempt' => $data['attempt'] ?? null,
    //         'pass_mark' => $data['pass_mark'],
    //         'time' => $data['time'] ?? null,
    //         'status' => (!empty($data['status']) and $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
    //         'certificate' => (!empty($data['certificate']) and $data['certificate'] == 'on') ? true : false,
    //         'created_at' => time(),
    //     ]);

    //     if (!empty($quiz)) {
    //         QuizTranslation::updateOrCreate([
    //             'quiz_id' => $quiz->id,
    //             'locale' => mb_strtolower($data['locale']),
    //         ], [
    //             'title' => $data['title'],
    //         ]);
    //     }

    //     if ($request->ajax()) {

    //         $redirectUrl = '';

    //         if (empty($data['is_webinar_page'])) {
    //             $redirectUrl = '/panel/quizzes/' . $quiz->id . '/edit';
    //         }

    //         return response()->json([
    //             'code' => 200,
    //             'redirect_url' => $redirectUrl
    //         ]);
    //     } else {
    //         return redirect()->route('panel_edit_quiz', ['id' => $quiz->id]);
    //     }
    // }

    public function store(Request $request)
    {
        $data = $request->get('ajax')['new'];
        $locale = $data['locale'] ?? getDefaultLocale();
        $rules = [
            'title' => 'required|max:255',
            'webinar_id' => 'required|exists:webinars,id',
            'pass_mark' => 'required',
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validate->errors()
            ], 422);
        }

        $user = auth()->user();

        $webinar = null;
        $chapter = null;
        $creator_id = null;

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])->first();
            $creator_id = $webinar->creator_id;
        } else {
            $creator_id = $user->id;
        }

        if (!empty($webinar)) {
            $chapter = null;

            if (!empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }

            $quiz = Quiz::create([
                'webinar_id' => $webinar->id,
                'chapter_id' => !empty($chapter) ? $chapter->id : null,
                'creator_id' => $creator_id,
                'text_lesson_id' => ($data['text_lesson_id'] == 0) ? null : $data['text_lesson_id'],
                'attempt' => $data['attempt'] ?? null,
                'pass_mark' => $data['pass_mark'],
                'time' => $data['time'] ?? null,
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
                'certificate' => (!empty($data['certificate']) and $data['certificate'] == 'on'),
                'display_questions_randomly' => (!empty($data['display_questions_randomly']) and $data['display_questions_randomly'] == 'on'),
                'expiry_days' => (!empty($data['expiry_days']) and $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
                'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
                'show_after_days' => (
                    (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
                    (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
                ) ? (int) $data['show_after_days'] : 0,
                'created_at' => time(),
            ]);

            QuizTranslation::updateOrCreate([
                'quiz_id' => $quiz->id,
                'locale' => mb_strtolower($locale),
            ], [
                'title' => $data['title'],
            ]);

            if (!empty($quiz->chapter_id)) {
                WebinarChapterItem::makeItem($webinar->creator_id, $quiz->chapter_id, $quiz->id, WebinarChapterItem::$chapterQuiz);
            }

            // Send Notification To All Students
            $webinar->sendNotificationToAllStudentsForNewQuizPublished($quiz);

            if ($request->ajax()) {

                $redirectUrl = '';

                if (empty($data['is_webinar_page'])) {
                    $redirectUrl = '/panel/quizzes/' . $quiz->id . '/edit';
                }

                return response()->json([
                    'code' => 200,
                    'redirect_url' => $redirectUrl
                ]);
            } else {
                return redirect()->route('panel_edit_quiz', ['id' => $quiz->id]);
            }
        } else {
            return back()->withErrors([
                'webinar_id' => trans('validation.exists', ['attribute' => trans('panel/main.course')])
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $user = auth()->user();
        $webinars = Webinar::where(function ($query) use ($user) {
            $query->where('teacher_id', $user->id)
                ->orWhere('creator_id', $user->id);
        })->get();

        $quiz = Quiz::where('id', $id)
            ->where('creator_id', $user->id)
            ->with([
                'quizQuestions' => function ($query) {
                    $query->with('quizzesQuestionsAnswers');
                },
            ])->first();
        if (!empty($quiz)) {
            $chapters = collect();

            if (!empty($quiz->webinar)) {
                $chapters = $quiz->webinar->chapters;
            }

            $lessons = TextLesson::where('chapter_id', $quiz->chapter_id)
                ->get();

            $locale = $request->get('locale', app()->getLocale());

            $webinarController = new WebinarController();

            $data = [
                'pageTitle' => trans('public.edit') . ' ' . $quiz->title,
                'webinars' => $webinars,
                'quiz' => $quiz,
                'quizQuestions' => $quiz->quizQuestions,
                'chapters' => $chapters,
                'lessons' => $lessons,
                'userLanguages' => $webinarController->getUserLanguagesLists(),
                'locale' => mb_strtolower($locale),
                'defaultLocale' => getDefaultLocale(),
            ];

            return view(getTemplate() . '.panel.quizzes.create', $data);
        }

        abort(404);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required|max:255',
            'webinar_id' => 'nullable',
            'pass_mark' => 'required',
        ];
        $data = $request->get('ajax')[$id];
        if ($request->ajax()) {
            $validate = Validator::make($data, $rules);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 422,
                    'errors' => $validate->errors()
                ], 422);
            }
        } else {
            $this->validate($request, $rules);
        }
        $user = auth()->user();
        $webinar = null;

        if ($data['show_after_days'] == 0) {
            $data['drip_feed'] = 0;
        } else {
            $data['drip_feed'] = 1;
        }

        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])
                ->where(function ($query) use ($user) {
                    $query->where('teacher_id', $user->id)
                        ->orWhere('creator_id', $user->id);
                })->first();

            if (!empty($webinar) and !empty($data['chapter_id'])) {
                $chapter = WebinarChapter::where('id', $data['chapter_id'])
                    ->where('webinar_id', $webinar->id)
                    ->first();
            }

            if (!empty($chapter) and !empty($data['lesson_id'])) {
                $lesson = TextLesson::where('id', $data['lesson_id'])
                    ->where('chapter_id', $chapter->id)
                    ->first();
            }
        }

        // if ($request->drip_feed == 0) {
        //     $request->show_after_days = null;
        // }
        $quiz = Quiz::find($id);
        $quiz->update([
            'webinar_id' => $data['webinar_id'] ? $data['webinar_id'] : null,
            'chapter_id' => $data['chapter_id'] ? $data['chapter_id'] : null,
            'attempt' => $data['attempt'] ?? null,
            'pass_mark' => $data['pass_mark'],
            'text_lesson_id' => ($data['text_lesson_id'] == 0) ? null : $data['text_lesson_id'],
            'time' => $data['time'] ?? null,
            'status' => (!empty($data['status']) and $data['status'] == 'on') ? Quiz::ACTIVE : Quiz::INACTIVE,
            'certificate' => (!empty($data['certificate']) and $data['certificate'] == 'on'),
            'display_limited_questions' => (!empty($data['display_limited_questions']) and $data['display_limited_questions'] == 'on'),
            'display_number_of_questions' => (!empty($data['display_limited_questions']) and $data['display_limited_questions'] == 'on' and !empty($data['display_number_of_questions'])) ? $data['display_number_of_questions'] : null,
            'display_questions_randomly' => (!empty($data['display_questions_randomly']) and $data['display_questions_randomly'] == 'on'),
            'expiry_days' => (!empty($data['expiry_days']) and $data['expiry_days'] > 0) ? $data['expiry_days'] : null,
            'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
            'show_after_days' => (
                (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
                (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
            ) ? (int) $data['show_after_days'] : 0,
            'updated_at' => time(),
        ]);

        QuizTranslation::updateOrCreate([
            'quiz_id' => $quiz->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'code' => 200
            ]);
        } else {
            return redirect('panel/quizzes');
        }
    }

    public function destroy(Request $request, $id)
    {
        $user_id = auth()->id();
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }
        $quiz = Quiz::where('id', $id)
            ->where('creator_id', $user_id)
            ->first();

        if (!empty($quiz)) {
            $quiz->delete();

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    /**
     * Function to show 'all' pending quizzes to Instructor, for which instructor has view access.
     * @return View
     */
    public function pending()
    {
        $user = auth()->user();
        //Only teacher should be able to view quizzes pending assessments
        if (!$user->isTeacher()) {
            abort(404);
        }
        $quizzesResults = $user->getAccessiblePendingAssessments();
        $quizzesResults = $quizzesResults->paginate(10);

        $data = [
            'pageTitle' => trans('panel.pending_quizzes'),
            'quizzesResults' => $quizzesResults,
        ];
        return view(getTemplate() . '.panel.quizzes.pending', $data);
    }

    public function start(Request $request, $id)
    {

        $user = auth()->user();
        $quizResult = QuizzesResult::where([
                "quiz_id" => $id,
                "user_id" => $user->id,
            ])->where('status','attempting')
            ->orderBy("id", "desc")
            ->first();
        $data = ['pageTitle' => trans('quiz.quiz_start')];
        $quiz = Quiz::where('id', $id)
            ->with([
                'quizQuestions' => function ($query) {
                    $query->with('quizzesQuestionsAnswers');
                },
            ])
            ->first();


        if ($quiz) {
            $valid = $quiz->attemptable($user);

            $webinarHelper = new WebinarHelper();
            $webinar = $webinarHelper->getWebinarByContentId($quiz->id, Webinar::$quiz);
            $nextLesson = $webinarHelper->getNextContent($quiz->id, Webinar::$quiz);
            $previousLesson = $webinarHelper->getPreviousContent($quiz->id, Webinar::$quiz);

            if ($valid === true) {
                $contentType = $previousLesson['content_type'] ?? null;
                $content = $previousLesson['content'] ?? null;

                while ($contentType === Webinar::$quiz && !empty($previousLesson)) {
                    $previousLesson = $webinarHelper->getPreviousContent($content->id, Webinar::$quiz);
                    $contentType = $previousLesson['content_type'] ?? null;
                    $content = $previousLesson['content'] ?? null;
                }
                $prefilledQuestions = $quiz->prefilledQuestions($user);
                if (empty($quizResult) || (!empty($quizResult) && $quizResult->status != QuizzesResult::$attempting)) {
                    $newQuizStart = QuizzesResult::create([
                        'quiz_id' => $quiz->id,
                        'user_id' => $user->id,
                        'results' => '',
                        'user_grade' => 0,
                        'status' => 'attempting',
                        'created_at' => time()
                    ]);
                } else {
                    $newQuizStart = $quizResult;
                }

                $userQuizCount = QuizzesResult::where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->count();
                $data = array_merge($data, [
                    'quiz' => $quiz,
                    'quizQuestions' => $quiz->quizQuestions,
                    'prefilledQuestions' => $prefilledQuestions,
                    'attempt_count' => $userQuizCount + 1,
                    'newQuizStart' => $newQuizStart,
                    'userAnswers' => (!empty($quizResult) && !empty($quizResult->results)) ? json_decode($quizResult->results, true) : null,
                    'quizResult' => $quizResult ? $quizResult : ''
                ]);
            } else {
                $prefilledQuestions = $quiz->prefilledQuestions($user);
                $quizAttemptErrorMessage = $quiz->getQuizAttemptError();
                $data = array_merge($data, [
                    'prefilledQuestions' => $prefilledQuestions,
                    'invalid' => true,
                    'validityErrorMessage' => $quizAttemptErrorMessage,
                    'quiz' => $quiz,
                    'nextLesson' => $nextLesson,
                    'nextLessonId' => $request->get('n'),
                    'courseSlug' => $request->get('s'),
                    'quizResult' => $quizResult ? $quizResult : ''
                ]);
            }

            $data = array_merge($data, [
                'previousLesson' => $previousLesson,
                'webinarId' => $webinar->id,
            ]);

            return view(getTemplate() . '.panel.quizzes.start', $data);
        }
        abort(404);
    }

    public function quizzesStoreResult(Request $request, $id)
    {
        $user = auth()->user();
        $quiz = Quiz::where('id', $id)->first();

        if ($quiz) {
            $results = $request->get('question');
            $quizResultId = $request->get('quiz_result_id');

            if (!empty($quizResultId)) {

                $quizResult = QuizzesResult::where('id', $quizResultId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!empty($quizResult)) {

                    $passMark = $quiz->pass_mark;
                    $totalMark = 0;
                    $status = '';
                    if (!empty($results)) {
                        foreach ($results as $questionId => $result) {

                            if (!is_array($result)) {
                                unset($results[$questionId]);
                            } else {

                                $question = QuizzesQuestion::where('id', $questionId)
                                    ->where('quiz_id', $quiz->id)
                                    ->first();
                                if (in_array($question->type, [QuizzesQuestion::$fillInBlank, QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage])) {
                                    $correctAnswer = json_decode($question->correct, true);
                                    $results[$questionId]['correctAnswer'] = $correctAnswer;
                                }

                                if (
                                    ($question and !empty($result['answer']))
                                    || (isset($result['type']) && $result['type'] === QuizzesQuestion::$fileUpload) //this will only be set for files
                                ) {
                                    if (isset($result['answer'])) {
                                        $answer = QuizzesQuestionsAnswer::where('id', $result['answer'])
                                            ->where('question_id', $question->id)
                                            // ->where('creator_id', $quiz->creator_id) //this causes issues - answers aren't fetched if question is defined by instructor other than quiz creator
                                            ->first();
                                    }

                                    $results[$questionId]['status'] = false;
                                    $results[$questionId]['grade'] = $question->grade;
                                    if (!is_null($request->file('question')) && $question->type === QuizzesQuestion::$fileUpload) {
                                        $uploadedFiles = $request->file('question')[$questionId]['answer'];
                                        $filePaths = [];
                                        $filesCount = 0;
                                        foreach ($uploadedFiles as $uploadedFile) {
                                            $extension = $uploadedFile->getClientOriginalExtension();
                                            $filename = $questionId . '_' . ($filesCount + 1) . '.' . $extension;

                                            $path = Storage::disk('local')->putFileAs(
                                                'quiz_docs/' . $quizResultId,
                                                $uploadedFile,
                                                $filename
                                            );
                                            $filePaths[] = $path;
                                            $filesCount++;
                                        }
                                        $results[$questionId]['answer'] = json_encode($filePaths);
                                    }

                                    if (in_array($question->type, [QuizzesQuestion::$multiple, QuizzesQuestion::$fillInBlank]) && isset($answer) && $answer and $answer->correct) {
                                        $results[$questionId]['status'] = true;
                                        $totalMark += (int)$question->grade;
                                    }

                                    if (in_array($question->type, [QuizzesQuestion::$descriptive, QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListText, QuizzesQuestion::$fileUpload])) {
                                        $status = 'waiting';
                                    }
                                }
                            }
                        }
                    }
                    /* $prefilledQuestions = json_decode($quizResult->results);
                    foreach($prefilledQuestions as $questionId => $preQuestion) {
                        $results[$questionId] = $preQuestion;
                        $totalMark += (int)$preQuestion->grade;
                    } */

                    if ( $request->has('finishQuizBtn') ) {
                        $status = ($totalMark >= $passMark) ? QuizzesResult::$passed : QuizzesResult::$waiting;
                    } else {
                        $status = !empty($quizResult->status) ? $quizResult->status : QuizzesResult::$attempting;
                    }

                    $results["attempt_number"] = $request->get('attempt_number');
                    if ($request->ajax() && !$request->has('finishQuizBtn')) {
                        $status = QuizzesResult::$attempting;
                        $quizResult->update([
                            'results' => json_encode($results),
                            'user_grade' => $totalMark,
                            'status' => $status,
                            'created_at' => time()
                        ]);

                        return response()->json([
                            'status' => true,
                            'message' => trans('quiz.asessment_progress_saved'),
                        ]);
                    } else {
                        $quizResult->update([
                            'results' => json_encode($results),
                            'user_grade' => $totalMark,
                            'status' => $status,
                            'created_at' => time()
                        ]);
                    }

                    if ($quizResult->status == QuizzesResult::$waiting) {
                        $notifyOptions = [
                            '[c.title]' => $quiz->webinar_title,
                            '[student.name]' => $user->full_name,
                            '[q.title]' => $quiz->title,
                        ];
                        $temp = "student_unit_submission";
                        $notifyOpt = [
                            "[student.name]" => $user->full_name
                        ];


                        sendNotification('waiting_quiz', $notifyOptions, $quiz->creator_id);
                        // sendNotification($temp, $notifyOpt, $user->id);//assessment complete notification
                    }
                    $details = getGeneralSettings();
                    $details["student_name"] = $user->full_name;
                    try{
                        \Mail::to($user->email)->send(new \App\Mail\ConfirmEnrollmentMail($details));
                    } catch(\Exception $e) {
                        $message = "Failed to send email after the quiz attempt!";
                        $message .= $e->getMessage();
                        \Log::error($message);
                    }
                    // \Mail::to('haris.zafar@provelopers.net')->send(new \App\Mail\ConfirmEnrollmentMail($generalSettings));
                    return redirect()->route('quiz_status', ['quizResultId' => $quizResult]);
                }
            }
        }
        abort(404);
    }

    public function downloadQuestionAttachment(Request $request)
    {
        $filePath = $request->get('filePath');
        return Storage::download($filePath);
    }

    public function status($quizResultId)
    {
        $user = auth()->user();

        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->where('user_id', $user->id)
            ->with(['quiz' => function ($query) {
                $query->with(['quizQuestions']);
            }])
            ->first();

        if ($quizResult) {
            $quiz = $quizResult->quiz;
            $attemptCount = $quiz->attempt;

            $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->count();

            $canTryAgain = false;
            if ($userQuizDone < $attemptCount) {
                $canTryAgain = true;
            }
            $webinarHelper = new WebinarHelper();
            $webinar = $webinarHelper->getWebinarByContentId($quiz->id, Webinar::$quiz);

            //get the next and previous content of the course
            $chapterItems = WebinarHelper::getChapterItems($webinar);
            $previous = WebinarHelper::getPreviousItem($quiz,$chapterItems, 'quiz');
            $next = WebinarHelper::getNextItem($quiz,$chapterItems ,'quiz');

            $data = [
                'pageTitle' => trans('quiz.quiz_status'),
                'quizResult' => $quizResult,
                'quiz' => $quiz,
                'quizQuestions' => $quiz->quizQuestions,
                'attempt_count' => $userQuizDone,
                'canTryAgain' => $canTryAgain,
                'webinarId' => $webinar->id,
                'next' => $next,
                'previous' => $previous,
                'slug' => $webinar->slug,
            ];

            return view(getTemplate() . '.panel.quizzes.status', $data);
        }
        abort(404);
    }

    public function myResults(Request $request)
    {
        //Is partial requested for displaying quiz results?
        if ($request->get('fetchQuizDataOnly') === true && (int)$request->get('userId') > 0) {
            $userId = (int)$request->get('userId');
            $requestedUser = User::find($userId);
            $requestingUser = auth()->user();
            if (
                $requestingUser->role_name === Role::$user
                && ($requestingUser->id !== $userId && $requestedUser->role_name === Role::$user)
            ) {
                abort(404);
            }
        } else {
            //otherwise default behaviour is to get the id of the logged in user
            $userId = auth()->user()->id;
        }
        $query = QuizzesResult::where('user_id', $userId);

        $quizResultsCount = deepClone($query)->count();
        $passedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$passed)->count();
        $failedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$failed)->count();
        $waitingCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$waiting)->count();

        $query = $this->resultFilters($request, deepClone($query));

        $quizResults = $query->with([
            'quiz' => function ($query) {
                $query->with(['quizQuestions', 'creator', 'webinar']);
            }
        ])->orderBy('created_at', 'desc')
            ->paginate(10);

        foreach ($quizResults->groupBy('quiz_id') as $quiz_id => $quizResult) {
            $canTryAgainQuiz = false;

            $result = $quizResult->first();
            $quiz = $result->quiz;

            if (!isset($quiz->attempt) or (count($quizResult) < $quiz->attempt and $result->status !== 'passed')) {
                $canTryAgainQuiz = true;
            }

            foreach ($quizResult as $item) {
                $item->can_try = $canTryAgainQuiz;
                if ($canTryAgainQuiz and isset($quiz->attempt)) {
                    $item->count_can_try = $quiz->attempt - count($quizResult);
                }
            }
        }

        $data = [
            'pageTitle' => trans('quiz.my_results'),
            'quizzesResults' => $quizResults,
            'quizzesResultsCount' => $quizResultsCount,
            'passedCount' => $passedCount,
            'failedCount' => $failedCount,
            'waitingCount' => $waitingCount
        ];

        //Cater request for returning only the quiz results partial
        if ($request->get('fetchQuizDataOnly') === true) {
            $data['disableActions'] = true;
            return (string) view(getTemplate() . '.panel.quizzes.partials.quiz_result_table_partial', $data);
        }

        return view(getTemplate() . '.panel.quizzes.my_results', $data);
    }

    public function opens(Request $request)
    {
        $user = auth()->user();

        $webinarIds = $user->getPurchasedCoursesIds();

        $query = Quiz::whereIn('webinar_id', $webinarIds)
            ->where('status', 'active')
            ->whereDoesntHave('quizResults', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        $query = $this->resultFilters($request, deepClone($query));

        $quizzes = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('quiz.open_quizzes'),
            'quizzes' => $quizzes
        ];

        return view(getTemplate() . '.panel.quizzes.opens', $data);
    }

    public function results(Request $request)
    {
        $user = auth()->user();

        if (!$user->isUser()) {
            $quizzes = Quiz::where('creator_id', $user->id)
                ->where('status', 'active')
                ->get();

            $quizzesIds = $quizzes->pluck('id')->toArray();

            //fetching quizzes for courses where instructor is a partner instructor
            $invitedWebinarsRelations = WebinarPartnerTeacher::where('teacher_id', $user->id)->get();
            $webinarIds = $invitedWebinarsRelations->pluck('webinar_id')->toArray();
            $invitedQuizzes = Quiz::whereIn('webinar_id', $webinarIds)
                ->where('status', Webinar::$active)
                ->get();
            $invitedQuizIds = $invitedQuizzes->pluck('id')->toArray();
            //if there were any invited course quizzes, let's fetch them as well
            if (isset($invitedQuizIds) && count($invitedQuizIds) > 0) {
                $quizzesIds = array_merge($quizzesIds, $invitedQuizIds);
            }

            $query = QuizzesResult::whereIn('quiz_id', $quizzesIds);

            $studentsIds = $query->pluck('user_id')->toArray();
            $allStudents = User::select('id', 'full_name')->whereIn('id', $studentsIds)->get();

            $quizResultsCount = $query->count();
            $quizAvgGrad = round($query->avg('user_grade'), 2);
            $waitingCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$waiting)->count();
            $passedCount = deepClone($query)->where('status', \App\Models\QuizzesResult::$passed)->count();
            $successRate = ($quizResultsCount > 0) ? round($passedCount / $quizResultsCount * 100) : 0;

            $query = $this->resultFilters($request, deepClone($query));

            $quizzesResults = $query->with([
                'quiz' => function ($query) {
                    $query->with(['quizQuestions', 'creator', 'webinar']);
                }, 'user'
            ])->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('quiz.results'),
                'quizzesResults' => $quizzesResults,
                'quizResultsCount' => $quizResultsCount,
                'successRate' => $successRate,
                'quizAvgGrad' => $quizAvgGrad,
                'waitingCount' => $waitingCount,
                'quizzes' => $quizzes,
                'allStudents' => $allStudents
            ];

            return view(getTemplate() . '.panel.quizzes.results', $data);
        }

        abort(404);
    }

    public function resultFilters(Request $request, $query)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $quiz_id = $request->get('quiz_id', null);
        $total_mark = $request->get('total_mark', null);
        $status = $request->get('status', null);
        $user_id = $request->get('user_id', null);
        $instructor = $request->get('instructor', null);
        $open_results = $request->get('open_results', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($quiz_id) and $quiz_id != 'all') {
            $query->where('quiz_id', $quiz_id);
        }

        if ($total_mark) {
            $query->where('total_mark', $total_mark);
        }

        if (!empty($user_id) and $user_id != 'all') {
            $query->where('user_id', $user_id);
        }

        if ($instructor) {
            $userIds = User::whereIn('role_name', [Role::$teacher, Role::$organization])
                ->where('full_name', 'like', '%' . $instructor . '%')
                ->pluck('id')->toArray();

            $query->whereIn('creator_id', $userIds);
        }

        if ($status and $status != 'all') {
            $query->where('status', strtolower($status));
        }

        if (!empty($open_results)) {
            $query->where('status', 'waiting');
        }

        return $query;
    }

    public function showResult($quizResultId)
    {
        $user = auth()->user();

        $quizzesIds = Quiz::where('creator_id', $user->id)->pluck('id')->toArray();

        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->where(function ($query) use ($user, $quizzesIds) {
                if ($user->role_name === Role::$user) {
                    /* Student should only be able to view their own results.
                    Admin, teacher and organization roles can view any student's result */
                    $query->where('user_id', $user->id)
                        ->orWhereIn('quiz_id', $quizzesIds);
                }
            })->with([
                'quiz' => function ($query) {
                    $query->with(['quizQuestions', 'webinar']);
                }
            ])->first();

        if (!empty($quizResult)) {
            $numberOfAttempt = QuizzesResult::where('quiz_id', $quizResult->quiz->id)
                ->where('user_id', $quizResult->user_id)
                ->count();

            $data = [
                'pageTitle' => trans('quiz.result'),
                'quizResult' => $quizResult,
                'userAnswers' => json_decode($quizResult->results, true),
                'numberOfAttempt' => $numberOfAttempt,
                'questionsSumGrade' => $quizResult->quiz->quizQuestions->sum('grade'),
            ];

            return view(getTemplate() . '.panel.quizzes.quiz_result', $data);
        }

        abort(404);
    }

    public function destroyQuizResult($quizResultId)
    {
        $user = auth()->user();

        $quizzesIds = Quiz::where('creator_id', $user->id)->pluck('id')->toArray();

        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->whereIn('quiz_id', $quizzesIds)
            ->first();

        if (!empty($quizResult)) {
            $quizResult->delete();

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function editResult($quizResultId)
    {
        $user = auth()->user();

        $quizzesIds = Quiz::where('creator_id', $user->id)->pluck('id')->toArray();
        if ($user->role_name !== Role::$user) {
            $correspondingQuiz = QuizzesResult::where('id', $quizResultId)->get()->pluck('quiz_id')->toArray();
            $allowedEditors = $this->getAllowedQuizEditors($correspondingQuiz);

            //fetching quizzes for courses where instructor is a partner instructor
            $invitedWebinarsRelations = WebinarPartnerTeacher::where('teacher_id', $user->id)->get();
            $webinarIds = $invitedWebinarsRelations->pluck('webinar_id')->toArray();
            $invitedQuizzes = Quiz::whereIn('webinar_id', $webinarIds)->get();
            $invitedQuizIds = $invitedQuizzes->pluck('id')->toArray();
            //if there were any invited course quizzes, let's fetch them as well
            if (isset($invitedQuizIds) && count($invitedQuizIds) > 0) {
                $quizzesIds = array_merge($invitedQuizIds, $quizzesIds);
            }
        }
        $quizResult = QuizzesResult::where('id', $quizResultId)
            ->whereIn('quiz_id', $quizzesIds)
            ->with([
                'quiz' => function ($query) {
                    $query->with([
                        'quizQuestions' => function ($query) {
                            $query->orderBy('id', 'asc');
                        },
                        'webinar'
                    ]);
                }
            ])->first();

        if (!empty($quizResult)) {
            $numberOfAttempt = QuizzesResult::where('quiz_id', $quizResult->quiz->id)
                ->where('user_id', $quizResult->user_id)
                ->count();

            $data = [
                'pageTitle' => trans('quiz.result'),
                'teacherReviews' => true,
                'allowedEditors' => $allowedEditors,
                'quizResult' => $quizResult,
                'newQuizStart' => $quizResult,
                'userAnswers' => json_decode($quizResult->results, true),
                'numberOfAttempt' => $numberOfAttempt,
                'questionsSumGrade' => $quizResult->quiz->quizQuestions->sum('grade'),
            ];
            return view(getTemplate() . '.panel.quizzes.quiz_result', $data);
        }

        abort(404);
    }

    public function updateResult(Request $request, $id)
    {
        $user = auth()->user();
        $quiz = Quiz::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();
        if (empty($quiz)) {
            if ($user->role_name !== Role::$user) {
                $allowedEditors = $this->getAllowedQuizEditors($id);
                $quiz = Quiz::where('id', $id)->first();
            }
        }

        if (!empty($quiz) || (isset($allowedEditors) && in_array($user->id, $allowedEditors))) {
            $reviews = $request->get('question');
            $quizResultId = $request->get('quiz_result_id');

            if (!empty($quizResultId)) {

                $quizResult = QuizzesResult::where('id', $quizResultId)
                    ->where('quiz_id', $quiz->id)
                    ->first();

                if (!empty($quizResult)) {

                    $oldResults = json_decode($quizResult->results, true);
                    $totalMark = 0;
                    $status = '';
                    $user_grade = $quizResult->user_grade;

                    if (!empty($oldResults) and count($oldResults)) {
                        foreach ($oldResults as $question_id => $result) {
                            if(!is_null($reviews)) {
                                foreach ($reviews as $question_id2 => $review) {
                                    if ($question_id2 == $question_id) {
                                        $question = QuizzesQuestion::where('id', $question_id)
                                            // removed check to allow partner instructors to assess quizzes on invitation courses
                                            // ->where('creator_id', $user->id)
                                            ->first();

                                        if (in_array($question->type, [QuizzesQuestion::$descriptive, QuizzesQuestion::$fillInBlank, QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage, QuizzesQuestion::$fileUpload])) {
                                            if (!empty($result['status']) and $result['status']) {
                                                $user_grade = $user_grade - (isset($result['grade']) ? (int)$result['grade'] : 0);
                                                $user_grade = $user_grade + (isset($review['grade']) ? (int)$review['grade'] : (int)$question->grade);
                                            } else if (isset($result['status']) and !$result['status']) {
                                                $user_grade = $user_grade + (isset($review['grade']) ? (int)$review['grade'] : (int)$question->grade);
                                                $oldResults[$question_id]['grade'] = isset($review['grade']) ? $review['grade'] : $question->grade;
                                            }
                                            /* Mark assessment against manual questions */
                                            if (isset($review['assessment'])) {
                                                $oldResults[$question_id]['assessment'] = $review['assessment'];
                                                if (isset($review['instructor_remarks'])) {
                                                    $oldResults[$question_id]['instructor_remarks'] = $review['instructor_remarks'];
                                                }
                                            }

                                            $oldResults[$question_id]['status'] = true;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (!empty($reviews) and count($reviews)) {
                        foreach ($reviews as $questionId => $review) {

                            if (!is_array($review)) {
                                unset($reviews[$questionId]);
                            } else {
                                $question = QuizzesQuestion::where('id', $questionId)
                                    ->where('quiz_id', $quiz->id)
                                    ->first();

                                if ($question and $question->type == 'descriptive') {
                                    $user_grade += (isset($review['grade']) ? (int)$review['grade'] : 0);
                                }
                            }
                        }

                        $oldResults = $reviews;
                    }

                    $quizResult->user_grade = $user_grade;
                    $passMark = $quiz->pass_mark;
                    //Notification based on Quiz result status
                    $template = 'quiz_result_failed';
                    $notifyOptions = [
                        '[c.title]' => $quiz->webinar_title,
                        '[q.title]' => $quiz->title,
                    ];
                    if ($quizResult->user_grade >= $passMark) { //Quiz Passed
                        $quizResult->status = QuizzesResult::$passed;
                        $template = 'quiz_result_passed';
                        $webinarHelper = new WebinarHelper();
                        $webinar = $webinarHelper->getWebinarByContentId($quiz->id, Webinar::$quiz);
                        $webinarHelper->courseCompletionNotification( $webinar );
                    } else { //Quiz Failed
                        $quizResult->status = QuizzesResult::$failed;
                        $notifyOptions['[link]']  = route('panel_quiz_results_list');
                    }

                    //Log instructor who made the assessment
                    $quizResult->assessed_by = $user->id;
                    $quizResult->results = json_encode($oldResults);

                    $quizResult->save();

                    sendNotification($template, $notifyOptions, $quizResult->user_id);

                    return redirect('panel/quizzes/results');
                }
            }
        }

        abort(404);
    }

    /**
     * Function to return allowed editors against quiz
     * @param integer|array $quizId
     * @return array
     */
    public function getAllowedQuizEditors($quizId)
    {
        if (is_array($quizId)) {
            $webinarId = Quiz::whereIn('id', $quizId)->get()->pluck('webinar_id')->toArray();
        } else {
            $webinarId = Quiz::where('id', $quizId)->first()->pluck('webinar_id')->toArray();
        }
        $correspondingWebinar = Webinar::whereIn('id', $webinarId)->first();
        $allowedEditors = [$correspondingWebinar->teacher_id];
        // print_r($allowedEditors) ;
        $invitedTeachers = WebinarPartnerTeacher::where('webinar_id', $correspondingWebinar->id)->get()->pluck('teacher_id')->toArray();
        return array_merge($allowedEditors, $invitedTeachers);
    }
}
