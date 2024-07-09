<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\AudioAttachment;

trait LessonAudioTrait
{

     /**
     * destroyAudio function is created to delete the lesson's audio
     *
     * @param int $audioId
     * @return boolean
     */
    public function destroyAudio(int $audioId) {
        $audio = AudioAttachment::where("id", $audioId)->first();
        if($audio) {
            $audio->delete();
            return true;
        }
        abort(404);
    }

    /**
     * createAudio function is used to create entry for the audio attachment
     *
     * @param Request $request
     * @param int $attachedBy
     * @param int $webinarId
     * @param int $textLessonId
     * @return void
     */
    public function createAudio(Request $request, int $attachedBy = null, int $webinarId, int $textLessonId){
        $newFileName = moveAudioFile($request);

        AudioAttachment::create([
            "attached_by" => $attachedBy,
            "webinar_id" => $webinarId,
            "text_lesson_id" => $textLessonId,
            "file_name" => $newFileName,
        ]);
    }

    /**
     * updateAudio function is used to update the audio attachment
     *
     * @param Request $request
     * @param int $attachedBy
     * @param int $webinarId
     * @param int $textLessonId
     * @return void
     */
    public function updateAudio(Request $request, int $attachedBy = null, int $webinarId, int $textLessonId){
        $newFileName = moveAudioFile($request);
        $audio = AudioAttachment::where("text_lesson_id", $textLessonId)->first();
        if($audio) {
            $audio->update([
                "attached_by" => $attachedBy,
                "webinar_id" => $webinarId,
                "text_lesson_id" => $textLessonId,
                "file_name" => $newFileName,
            ]);
        } else {
            AudioAttachment::create([
                "attached_by" => $attachedBy,
                "webinar_id" => $webinarId,
                "text_lesson_id" => $textLessonId,
                "file_name" => $newFileName,
            ]);
        }
    }
}
