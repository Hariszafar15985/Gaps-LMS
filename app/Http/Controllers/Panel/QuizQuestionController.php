<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use Illuminate\Http\Request;
use App\Models\Quiz;
use Illuminate\Support\Facades\Validator;

class QuizQuestionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->get('ajax');
        $rules = [
            'quiz_id' => 'required|exists:quizzes,id',
            'title' => 'required',
            'grade' => 'required|integer',
            'type' => 'required',
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validate->errors()
            ], 422);
        }

        $user = auth()->user();

        if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) {
            $answers = $data['answers'];

            $hasCorrect = false;
            foreach ($answers as $answer) {
                if (isset($answer['correct'])) {
                    $hasCorrect = true;
                }
            }

            if (!$hasCorrect) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'current_answer' => [trans('quiz.current_answer_required')]
                    ],
                ], 422);
            }
        }

        $quiz = Quiz::where('id', $data['quiz_id'])
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($quiz)) {
            $quizQuestionData = [
                'quiz_id' => $data['quiz_id'],
                'creator_id' => $user->id,
                'grade' => $data['grade'],
                'type' => $data['type'],
                'created_at' => time(),
                'title' => $data['title'],
                'order' => 0,
            ];
            //Handling Fill In The Blank
            if($data['type'] == QuizzesQuestion::$fillInBlank) {
                $data["answer"] = $request->get('answer');

            } elseif (in_array($data['type'],  [QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage])) {
                //Matching List questions
                $data["answer"] = $request->get('answers');
                if($data['type'] === QuizzesQuestion::$matchingListImage
                    && isset($data['answer']) && count($data['answer']) > 0) {
                        $pairs = $data['answer'];
                        foreach($pairs as $key => $pair) {
                            unset($data['answer'][$key]['file']);
                        }
                }
            }
            //Store the Blank Answers
            if(isset($data['answer']) && is_array($data['answer'])) {
                $quizQuestionData['correct'] = $data['correct'] = json_encode($data['answer']);
            }

            $quizQuestion = QuizzesQuestion::create($quizQuestionData);

            if (!empty($quizQuestion)) {
                QuizzesQuestionTranslation::updateOrCreate([
                    'quizzes_question_id' => $quizQuestion->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'correct' => $data['correct'] ?? null,
                ]);
            }

            $quiz->increaseTotalMark($quizQuestion->grade);

            if ($quizQuestion->type == QuizzesQuestion::$multiple and !empty($data['answers'])) {

                foreach ($answers as $answer) {
                    if (!empty($answer['title']) or !empty($answer['file'])) {
                        $questionAnswer = QuizzesQuestionsAnswer::create([
                            'question_id' => $quizQuestion->id,
                            'creator_id' => $user->id,
                            'image' => $answer['file'],
                            'correct' => isset($answer['correct']) ? true : false,
                            'created_at' => time()
                        ]);

                        if (!empty($questionAnswer)) {
                            QuizzesQuestionsAnswerTranslation::updateOrCreate([
                                'quizzes_questions_answer_id' => $questionAnswer->id,
                                'locale' => mb_strtolower($data['locale']),
                            ], [
                                'title' => $answer['title'],
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([
            'code' => 422
        ], 422);
    }

    public function edit(Request $request, $question_id)
    {
        $user = auth()->user();

        $question = QuizzesQuestion::where('id', $question_id)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($question)) {
            $quiz = Quiz::find($question->quiz_id);
            if (!empty($quiz)) {

                $locale = $request->get('locale', app()->getLocale());

                $webinarController = new WebinarController();

                $data = [
                    'pageTitle' => $question->title,
                    'correct' => $question->correct,
                    'quiz' => $quiz,
                    'question_edit' => $question,
                    'userLanguages' => $webinarController->getUserLanguagesLists(),
                    'locale' => mb_strtolower($locale),
                    'defaultLocale' => getDefaultLocale(),
                ];

                if ($question->type == 'multiple') {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.multiple_question', $data);
                } elseif ($question->type == QuizzesQuestion::$fillInBlank) {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.fill_in_the_blank_question', $data);
                } elseif ($question->type == QuizzesQuestion::$fileUpload) {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.file_upload_question', $data);
                } elseif ($question->type == QuizzesQuestion::$matchingListText) {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.matching_list_text_question', $data);
                } elseif ($question->type == QuizzesQuestion::$matchingListImage) {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.matching_list_image_question', $data);
                } elseif ($question->type == QuizzesQuestion::$informationText) {
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.information_text', $data);
                } else {
                    //descriptive question as fallback case
                    $html = (string)\View::make(getTemplate() . '.panel.quizzes.modals.descriptive_question', $data);
                }

                return response()->json([
                    'html' => $html
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function getQuestionByLocale(Request $request, $id)
    {
        $user = auth()->user();

        $question = QuizzesQuestion::where('id', $id)
            ->where('creator_id', $user->id)
            ->with('quizzesQuestionsAnswers')
            ->first();

        if (!empty($question)) {
            $locale = $request->get('locale', app()->getLocale());

            foreach ($question->translatedAttributes as $attribute) {
                try {
                    $question->$attribute = $question->translate(mb_strtolower($locale))->$attribute;
                } catch (\Exception $e) {
                    $question->$attribute = null;
                }
            }

            if (!empty($question->quizzesQuestionsAnswers) and count($question->quizzesQuestionsAnswers)) {
                foreach ($question->quizzesQuestionsAnswers as $answer) {
                    foreach ($answer->translatedAttributes as $att) {
                        try {
                            $answer->$att = $answer->translate(mb_strtolower($locale))->$att;
                        } catch (\Exception $e) {
                            $answer->$att = null;
                        }
                    }
                }
            }

            return response()->json([
                'question' => $question
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $data = $request->get('ajax');

        $rules = [
            'quiz_id' => 'required|exists:quizzes,id',
            'title' => 'required',
            'grade' => 'required',
            'type' => 'required',
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validate->errors()
            ], 422);
        }

        if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) {
            $answers = $data['answers'];

            $hasCorrect = false;
            foreach ($answers as $answer) {
                if (isset($answer['correct'])) {
                    $hasCorrect = true;
                }
            }

            if (!$hasCorrect) {
                return response([
                    'code' => 422,
                    'errors' => [
                        'current_answer' => [trans('quiz.current_answer_required')]
                    ],
                ], 422);
            }
        }


        $user = auth()->user();

        $quiz = Quiz::where('id', $data['quiz_id'])
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($quiz)) {
            $quizQuestion = QuizzesQuestion::where('id', $id)
                ->where('creator_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->first();

            if (!empty($quizQuestion)) {
                $quiz_total_grade = $quiz->total_mark - $quizQuestion->grade;

                $updateData = [
                    'quiz_id' => $data['quiz_id'],
                    'creator_id' => $user->id,
                    'grade' => $data['grade'],
                    'type' => $data['type'],
                    'updated_at' => time()
                ];

                //Handling Fill In The Blank
                if($data['type'] == QuizzesQuestion::$fillInBlank) {
                    $data["answer"] = $request->get('answer');

                } elseif (in_array($data['type'],  [QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage])) {
                    //Matching List questions
                    $data["answer"] = $request->get('answers');
                    if($data['type'] === QuizzesQuestion::$matchingListImage
                    && isset($data['answer']) && count($data['answer']) > 0) {
                        $pairs = $data['answer'];
                        foreach($pairs as $key => $pair) {
                            unset($data['answer'][$key]['file']);
                        }
                    }
                }
                //Store the Blank Answers
                if(isset($data['answer']) && is_array($data['answer'])) {
                    $quizQuestionData['correct'] = $data['correct'] = json_encode($data['answer']);
                }

                $quizQuestion->update($updateData);

                QuizzesQuestionTranslation::updateOrCreate([
                    'quizzes_question_id' => $quizQuestion->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'correct' => $data['correct'] ?? null,
                ]);

                $quiz_total_grade = ($quiz_total_grade > 0 ? $quiz_total_grade : 0) + $data['grade'];
                $quiz->update(['total_mark' => $quiz_total_grade]);;

                if ($data['type'] == QuizzesQuestion::$multiple and !empty($data['answers'])) {
                    $answers = $data['answers'];

                    if ($quizQuestion->type == QuizzesQuestion::$multiple and $answers) {
                        $oldAnswerIds = QuizzesQuestionsAnswer::where('question_id', $quizQuestion->id)->pluck('id')->toArray();

                        foreach ($answers as $key => $answer) {
                            if (!empty($answer['title']) or !empty($answer['file'])) {

                                if (count($oldAnswerIds)) {
                                    $oldAnswerIds = array_filter($oldAnswerIds, function ($item) use ($key) {
                                        return $item != $key;
                                    });
                                }

                                $quizQuestionsAnswer = QuizzesQuestionsAnswer::where('id', $key)->first();

                                if (!empty($quizQuestionsAnswer)) {
                                    $quizQuestionsAnswer->update([
                                        'question_id' => $quizQuestion->id,
                                        'creator_id' => $user->id,
                                        'image' => $answer['file'],
                                        'correct' => isset($answer['correct']) ? true : false,
                                        'created_at' => time()
                                    ]);
                                } else {
                                    $quizQuestionsAnswer = QuizzesQuestionsAnswer::create([
                                        'question_id' => $quizQuestion->id,
                                        'creator_id' => $user->id,
                                        'image' => $answer['file'],
                                        'correct' => isset($answer['correct']) ? true : false,
                                        'created_at' => time()
                                    ]);
                                }

                                if ($quizQuestionsAnswer) {
                                    QuizzesQuestionsAnswerTranslation::updateOrCreate([
                                        'quizzes_questions_answer_id' => $quizQuestionsAnswer->id,
                                        'locale' => mb_strtolower($data['locale']),
                                    ], [
                                        'title' => $answer['title'],
                                    ]);
                                }
                            }
                        }

                        if(count($oldAnswerIds)) {
                            QuizzesQuestionsAnswer::whereIn('id', $oldAnswerIds)->delete();
                        }
                    }
                }

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([
            'code' => 422
        ], 422);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        // if (!$user->isAdmin()) {
        //     abort(403);
        // }
        QuizzesQuestion::where('id', $id)
            ->where('creator_id', auth()->user()->id)
            ->delete();

        return response()->json([
            'code' => 200
        ], 200);
    }

}
