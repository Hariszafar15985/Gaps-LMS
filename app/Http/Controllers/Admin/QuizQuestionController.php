<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Models\QuizzesQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\QuizzesQuestionsAnswer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;

class QuizQuestionController extends Controller
{
    public function store(Request $request)
    {
        $answersData = $request->get('ajax');
        $data = $request->all();
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

        if ($data['type'] == QuizzesQuestion::$multiple and !empty($answersData['answers'])) {
            $answers = $answersData['answers'];

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

        $quiz = Quiz::where('id', $data['quiz_id'])->first();

        if (!empty($quiz)) {
            $creator = $quiz->creator;
            $order = QuizzesQuestion::query()->where('quiz_id', $quiz->id)->count() + 1;

            $quizQuestion = QuizzesQuestion::create([
                'quiz_id' => $data['quiz_id'],
                'creator_id' => $creator->id,
                'grade' => $data['grade'],
                'type' => $data['type'],
                'image' => $data['image'] ?? null,
                'video' => $data['video'] ?? null,
                'order' => $order,
                'created_at' => time()
            ]);

            //Handling Fill In The Blank
            if ($data['type'] == QuizzesQuestion::$fillInBlank) {
                $data["answer"]  = $data['answer'];
            } elseif (in_array($data['type'],  [QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage])) {
                //Matching List questions
                $data["answer"] = $data['answers'];
                unset($data['answers']);
            }
            //Store the Blank Answers
            if (isset($data['answer']) && is_array($data['answer'])) {
                $data['correct'] = json_encode($data['answer']);
            }

            if ($quizQuestion->type == QuizzesQuestion::$multiple and !empty($answersData['answers'])) {

                foreach ($answers as $answer) {
                    if (!empty($answer['title']) or !empty($answer['file'])) {
                        $questionAnswer = QuizzesQuestionsAnswer::create([
                            'question_id' => $quizQuestion->id,
                            'creator_id' => $creator->id,
                            'image' => $answer['file'] ?? null,
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


            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([
            'code' => 422,
            'message' => 'Quiz not found.'
        ], 422);
    }

    public function edit($question_id, Request $request)
    {
        $question = QuizzesQuestion::where('id', $question_id)->first();

        if (!empty($question)) {
            $quiz = Quiz::find($question->quiz_id);

            if (!empty($quiz)) {
                $locale = app()->getLocale();

                $data = [
                    'pageTitle' => $question->title,
                    'quiz' => $quiz,
                    'question_edit' => $question,
                    'locale' => mb_strtolower($locale),
                    'defaultLocale' => getDefaultLocale(),
                ];
                if ($question->type == 'multiple') {
                    $html = (string)\View::make('admin.quizzes.modals.multiple_question', $data);
                } elseif ($question->type == QuizzesQuestion::$fillInBlank) {
                    $html = (string)\View::make('admin.quizzes.modals.fill_in_the_blank_question', $data);
                } elseif ($question->type == QuizzesQuestion::$fileUpload) {
                    $html = (string)\View::make('admin.quizzes.modals.file_upload_question', $data);
                } elseif ($question->type == QuizzesQuestion::$matchingListText) {
                    $html = (string)\View::make('admin.quizzes.modals.matching_list_text_question', $data);
                } elseif ($question->type == QuizzesQuestion::$matchingListImage) {
                    $html = (string)\View::make('admin.quizzes.modals.matching_list_image_question', $data);
                } elseif ($question->type == QuizzesQuestion::$informationText) {
                    $html = (string)\View::make('admin.quizzes.modals.information_text', $data);
                } else {
                    //descriptive question as fallback case
                    $html = (string)\View::make('admin.quizzes.modals.descriptive_question', $data);
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
        $answersData = $request->get('ajax');
        $data = $request->all();
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


        if ($data['type'] == QuizzesQuestion::$multiple and !empty($answersData['answers'])) {
            $answers = $answersData['answers'];

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

        $quiz = Quiz::where('id', $request->input('quiz_id'))->first();

        if (!empty($quiz)) {
            $creator = $quiz->creator;

            $quizQuestion = QuizzesQuestion::where('id', $id)
                ->where('creator_id', $creator->id)
                ->where('quiz_id', $quiz->id)
                ->first();


            if (!empty($quizQuestion)) {
                $quiz->decreaseTotalMark($quizQuestion->grade);

                $quizQuestion->update([
                    'quiz_id' => $data['quiz_id'],
                    'creator_id' => $creator->id,
                    'grade' => $data['grade'],
                    'type' => $data['type'],
                    'updated_at' => time()
                ]);

                //Handling Fill In The Blank
                if ($data['type'] == QuizzesQuestion::$fillInBlank) {
                    $data["answer"] = $request->get('answer');
                } elseif (in_array($data['type'],  [QuizzesQuestion::$matchingListText, QuizzesQuestion::$matchingListImage])) {
                    //Matching List questions
                    $data["answer"] = $request->get('answers');
                }
                //Store the Blank Answers
                if (isset($data['answer']) && is_array($data['answer'])) {
                    $data['correct'] = json_encode($data['answer']);
                }

                QuizzesQuestionTranslation::updateOrCreate([
                    'quizzes_question_id' => $quizQuestion->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'correct' => $data['correct'] ?? null,
                ]);

                $quiz->increaseTotalMark($quizQuestion->grade);


                if ($quizQuestion->type == QuizzesQuestion::$multiple && $answers) {

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
                                    'creator_id' => $creator->id,
                                    'image' => $answer['file'],
                                    'correct' => isset($answer['correct']) ? true : false,
                                    'created_at' => time()
                                ]);
                            } else {
                                $quizQuestionsAnswer = QuizzesQuestionsAnswer::create([
                                    'question_id' => $quizQuestion->id,
                                    'creator_id' => $creator->id,
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

                    if (count($oldAnswerIds)) {
                        QuizzesQuestionsAnswer::whereIn('id', $oldAnswerIds)->delete();
                    }
                }

                removeContentLocale();

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        removeContentLocale();

        return response()->json([
            'code' => 422
        ], 422);
    }

    public function destroy(Request $request, $id)
    {
        QuizzesQuestion::where('id', $id)
            ->delete();

        return redirect()->back();
    }
}
