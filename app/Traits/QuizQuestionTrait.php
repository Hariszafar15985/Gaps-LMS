<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\QuizzesQuestion;

trait QuizQuestionTrait
{

    /**
     * Method to re-ordering the question questions
     *
     * @param Request $request
     * @return void
     */
    public function reOrderQuestions(Request $request) {
        $questionIds = explode("," , $request["data"]);
        $order = 1;
        foreach($questionIds as $questionId){
            if(!empty($questionId)) {
                QuizzesQuestion::where("id", $questionId)
                ->update([
                    "order" => $order,
                ]);
                $order = $order + 1;
            }
        }
        return true;
    }

}

