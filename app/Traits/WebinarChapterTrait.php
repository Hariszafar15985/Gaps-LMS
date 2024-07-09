<?php

namespace App\Traits;

use App\Models\File;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\Webinar;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\WebinarAssignment;
use App\Models\Api\WebinarChapterItem;
use Illuminate\Support\Facades\Validator;

//WebinarChapterTrait is used to keep the common methods for both admin and panel side
trait WebinarChapterTrait {

    public function change(Request $request)
    {
        $user = auth()->user();
        $data = $request->get('ajax');

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'item_type' => 'required',
            'chapter_id' => 'required',
            'webinar_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = null;

        $webinar = Webinar::find($data['webinar_id']);

        if (!empty($webinar) and $webinar->canAccess($user)) {

            switch ($data['item_type']) {
                case WebinarChapterItem::$chapterSession:
                    $item = Session::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterFile:
                    $item = File::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterTextLesson:
                    $item = TextLesson::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterQuiz:
                    $item = Quiz::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;

                case WebinarChapterItem::$chapterAssignment:
                    $item = WebinarAssignment::where('id', $data['item_id'])
                        ->where('webinar_id', $data['webinar_id'])
                        ->first();
                    break;
            }
        }

        if (!empty($item)) {
            $item->update([
                'chapter_id' => !empty($data['chapter_id']) ? $data['chapter_id'] : null
            ]);

            // following if block code will change the chapter id of quizzzes associated with a text lesson
            if (WebinarChapterItem::$chapterTextLesson) {

                WebinarChapterItem::where('item_id', $item->id)
                ->where('type', $data['item_type'])
                ->delete();

                if (!empty($data['chapter_id'])) {
                    WebinarChapterItem::makeItem($user->id, $data['chapter_id'], $item->id,$data['item_type']);
                }

                if (!blank($item->quizzes) && count($item->quizzes)) {
                    foreach ($item->quizzes as $lessonQuiz) {
                        $lessonQuiz->update(['chapter_id' => !empty($data['chapter_id']) ? $data['chapter_id'] : null]);
                        WebinarChapterItem::where('item_id', $lessonQuiz->id)->where('type','quiz')->delete();
                        if (!empty($data['chapter_id'])) {
                            WebinarChapterItem::makeItem($user->id, $data['chapter_id'], $lessonQuiz->id, 'quiz');
                        }

                    }
                }
            } else {

                WebinarChapterItem::where('item_id', $item->id)
                    ->where('type', $data['item_type'])
                    ->delete();

                if (!empty($data['chapter_id'])) {
                    WebinarChapterItem::makeItem($user->id, $data['chapter_id'], $item->id, $data['item_type']);
                }
            }
        }

        return response()->json([
            'code' => 200
        ], 200);
    }
}
