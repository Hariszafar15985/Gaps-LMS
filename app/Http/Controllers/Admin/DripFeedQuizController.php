<?php

namespace App\Http\Controllers\Admin;

use App\DripFeedQuiz;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;

class DripFeedQuizController extends Controller
{
    function showManually(Request $request)
    {
        $quizId = $request->quiz_id;
        $userId = $request->user_id;
        $quiz = Quiz::find($quizId);
        $webinarId = $quiz->webinar_id;
        $userHasManualAccess = DripFeedQuiz::where([
            "quiz_id" => $quizId,
            "user_id" => $userId,
        ])->first();
        if ($userHasManualAccess) {
            $userHasManualAccess->delete();
        } else {
            DripFeedQuiz::create([
                "user_id" => $userId,
                "quiz_id" => $quizId,
                "webinar_id" => $webinarId,
            ]);
        }
        return back();
    }
}
