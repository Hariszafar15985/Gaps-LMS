<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourseNotes;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;

class CourseNotesController extends Controller
{
    function saveNotes(Request $request)
    {
        $user = auth()->user();
        if ($request->type == 'text_lesson') {

            $checkData = CourseNotes::where([
                "user_id" => $user->id,
                "lesson_id" => $request->lessonId,
                "webinar_id" => $request->webinarId,
            ])->first();
        } elseif ($request->type == 'file') {
            $checkData = CourseNotes::where([
                "user_id" => $user->id,
                "file_id" => $request->fileId,
                "webinar_id" => $request->webinarId,
            ])->first();
        }

        if ($checkData) {

            $addNote = $checkData->update([
                "note_text" => $request->description,
            ]);
            if ($addNote) {
                return response()->json(["status" => "success", "message" => trans("public.notes_updated")]);
            } else {
                return response()->json(["status" => "error", "message" => "There is any error, please try again"]);
            }
        } else {
            $itemId = null;
            if (isset($request->fileId) && $request->fileId) {
                $itemId = $request->fileId;
            }

            if (isset($request->lessonId) && $request->lessonId) {
                $itemId = $request->lessonId;
            }
            $addNote = CourseNotes::create([
                "user_id" => $user->id,
                "webinar_id" => $request->webinarId,
                ($request->type == 'text_lesson') ? "lesson_id"  : "file_id" => $itemId,
                "type" => $request->type,
                "note_text" => $request->description,
            ]);
        }

        if ($addNote) {
            return response()->json(["status" => "success", "message" => trans("public.notes_saved")]);
        } else {
            return response()->json(["status" => "error", "message" => "There is any error, please try again"]);
        }
    }
}
