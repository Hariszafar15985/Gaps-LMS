<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;


class WebinarQuizController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('admin_webinars_edit');

        $this->validate($request, [
            'webinar_id' => 'required|exists:webinars,id',
            'quiz_id' => 'required',
        ]);

        $data = $request->all();

        $quiz = Quiz::where('id', $data['quiz_id'])
            // ->where('webinar_id', null)
            ->first();

        if (!empty($quiz)) {
            $quiz->webinar_id = $data['webinar_id'];
            $quiz->chapter_id = $data['chapter_id'] ?? null;
            $quiz->drip_feed = $data['drip_feed'];
            $quiz->show_after_days = $data['show_after_days'] ?? null;
            $quiz->save();

            return response()->json([
                'code' => 200,
            ], 200);
        }

        return response()->json([], 422);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinarQuiz = Quiz::where('id', $id)
            ->first();

        if (!empty($webinarQuiz)) {

            return response()->json([
                'webinarQuiz' => $webinarQuiz
            ], 200);
        }


        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $this->validate($request, [
            'webinar_id' => 'required',
            'quiz_id' => 'required',
        ]);

        $data = $request->all();

        $quiz = Quiz::findOrFail($data['quiz_id']);
        if($request->drip_feed == 0){
            $request->show_after_days = null;
        }
        if (!empty($quiz)) {
            $quiz->webinar_id = $data['webinar_id'];
            $quiz->chapter_id = $data['chapter_id'] ?? null;
            $quiz->text_lesson_id = $data['lesson_,'] ?? null;
            $quiz->drip_feed = $request->drip_feed;
            $quiz->show_after_days = $request->show_after_days ?? null;
            $quiz->save();

            return response()->json([
                'code' => 200,
            ], 200);
        }

        return response()->json([], 422);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $quiz = Quiz::where('id', $id)->first();

        if (!empty($quiz)) {
            $quiz->delete();
        }

        return redirect()->back();
    }
}
