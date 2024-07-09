<?php

namespace App\Traits;

use App\Models\QuizzesResult;

trait QuizTrait
{
    public $quizAttemptFailureMessage = null;

    public function isReleased()
    {
        $user = auth()->user();
        $canAttempt = false;
        if ($user) {

            if ($user->isAdmin()) {
                return true;
            }
            $userId = $user->id;
            $canAttempt = true;
            if(!userCanAttemptQuiz($this->id, $userId)){
                $canAttempt = false;
            }
        }
        return $canAttempt;
    }

    public function attemptable($user)
    {
        $valid = true;
        $validityErrorMessage = "";

        $statusWaitingCount = QuizzesResult::where('quiz_id', $this->id)
            ->where('user_id', $user->id)
            ->where('status', 'waiting')
            ->count();

        if ($statusWaitingCount > 0) {
            //an attempt against this quiz has already been submitted. Wait before results are announced
            // return back()->with('quiz_attempt_error', trans('quiz.cant_start_result_pending'));
            //continue;
            $valid = false;
            $validityErrorMessage = trans('quiz.cant_start_result_pending');
        } else {
            $userQuizCount = QuizzesResult::where('quiz_id', $this->id)
                ->where('user_id', $user->id)
                ->where('status', '!=', QuizzesResult::$attempting)
                ->count();
            $statusPass = QuizzesResult::where('quiz_id', $this->id)
                ->where('user_id', $user->id)
                ->where('status', QuizzesResult::$passed)
                ->exists();

            //an attempt against the quiz is set and user has exceeded attempts or the status is passed
            if (!(!isset($this->attempt) or ($userQuizCount  < $this->attempt and !$statusPass))) {
                $valid = false;
                $validityErrorMessage = trans('quiz.cant_start_quiz');
            }

            //let's prevent the user from re-attempting if they've already passed as well
            if ($statusPass) {
                $valid = false;
                $validityErrorMessage = trans('quiz.quiz_already_passed');
            }

        }
        if($valid){
            if(!$this->isReleased()){
                $valid = false;
                $validityErrorMessage = trans('public.quiz_unavailable');
            }
            /* if($this->id == 66)
                    dd($valid); */
        }
        if (!$valid) {
            $this->quizAttemptFailureMessage = $validityErrorMessage;
        }
        return $valid;
    }

    public function getQuizAttemptError()
    {
        return $this->quizAttemptFailureMessage;
    }

    public function prefilledQuestions($user)
    {
        $prefilledQuestions = null;
        $lastQuiz = QuizzesResult::where('quiz_id', $this->id)
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->first();
        //let's fetch all questions that have already been answered correctly in the last quiz
        if (isset($lastQuiz) && isset($lastQuiz['results'])) {
            $lastQuizQuestions = json_decode($lastQuiz['results'], true);
            $prefilledQuestions = array();
            if ($lastQuizQuestions) {
                foreach ($lastQuizQuestions as $questionId => $question) {
                    /*  Question has been reviewed/marked AND
                                ((Descriptive Question) OR (Non-descriptive / Multi-choice Question marked correct)) */
                    if (
                        (isset($question['status']) && $question['status'])
                        && (isset($question['assessment']) || (!isset($question['assessment']) && (int)$question['grade'] > 0)
                        )
                    ) {
                        $prefilledQuestions[$questionId] = $question;
                        $prefilledQuestions[$questionId]['status'] = false;
                    }
                }
            }
        }

        return $prefilledQuestions;
    }
}
