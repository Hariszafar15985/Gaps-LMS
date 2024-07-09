<?php

namespace App\Http\Controllers\Panel;

use Validator;
use App\Models\File;
use App\Models\Webinar;
use App\Models\AuditTrail;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\AudioAttachment;
use Illuminate\Validation\Rule;
use App\Traits\LessonAudioTrait;
use App\Models\WebinarChapterItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TextLessonAttachment;
use App\Traits\CourseDuplicatorTrait;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\TextLessonTranslation;

class TextLessonsController extends Controller
{
    use CourseDuplicatorTrait;
    use LessonAudioTrait;

    public function deleteAudio($id) {
        $deleteAudio = $this->destroyAudio($id); // this method is created in LessonAudioTrait
        if($deleteAudio) {
            $toastData['title'] = trans('public.audio_deleted');
            $toastData['msg']  = trans('public.audio_deleted_successfuly');
            $toastData['status'] = $status = 'success';
            return redirect()->back()->with([
                $status => $status,
                'toast' => $toastData,
            ]);
        }
        abort(404);
    }

    // public function store(Request $request)
    // {
    //     $user = auth()->user();
    //     $data = $request->get('ajax')['new'];

    //     $validator = Validator::make($data, [
    //         'webinar_id' => 'required',
    //         'chapter_id' => 'required',
    //         'title' => 'required',
    //         'study_time' => 'required|numeric',
    //         'image' => 'required',
    //         'accessibility' => 'required|' . Rule::in(File::$accessibility),
    //         'summary' => 'required',
    //         'content' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response([
    //             'code' => 422,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $webinar = Webinar::find($data['webinar_id']);

    //     if (!empty($webinar) and $webinar->canAccess($user)) {
    //         $lessonsCount = TextLesson::where('creator_id', $user->id)
    //             ->where('webinar_id', $data['webinar_id'])
    //             ->count();

    //         $textLesson = TextLesson::create([
    //             'creator_id' => $user->id,
    //             'webinar_id' => $data['webinar_id'],
    //             'chapter_id' => $data['chapter_id'],
    //             'image' => $data['image'],
    //             'study_time' => $data['study_time'],
    //             'accessibility' => $data['accessibility'],
    //             'order' => $lessonsCount + 1,
    //             'status' => (!empty($data['status']) and $data['status'] == 'on') ? TextLesson::$Active : TextLesson::$Inactive,
    //             'created_at' => time(),
    //             'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
    //             'show_after_days' => (
    //                 (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
    //                 (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
    //             ) ? (int) $data['show_after_days'] : 0,
    //         ]);

    //         if ($textLesson) {
    //             TextLessonTranslation::updateOrCreate([
    //                 'text_lesson_id' => $textLesson->id,
    //                 'locale' => mb_strtolower($data['locale']),
    //             ], [
    //                 'title' => $data['title'],
    //                 'summary' => $data['summary'],
    //                 'content' => $data['content'],
    //             ]);

    //             if (!empty($data['attachments'])) {
    //                 $attachments = $data['attachments'];
    //                 $this->saveAttachments($textLesson, $attachments);
    //             }
    //         }

    //         if($request->has("audio-file")) {
    //             $this->createAudio($request, $user->id, $data["webinar_id"], $textLesson->id);
    //         }

    //         return response()->json([
    //             'code' => 200,
    //         ], 200);
    //     }

    //     abort(403);
    // }

    public function store(Request $request)
    {
        $data = $request->get('ajax')['new'];
        $validator = Validator::make($data, [
            'webinar_id' => 'required',
            'title' => 'required',
            'study_time' => 'required|numeric',
            'image' => 'required',
            'accessibility' => 'required|' . Rule::in(File::$accessibility),
            'summary' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
            $data['access_after_day'] = !empty($data['access_after_day']) ? $data['access_after_day'] : null;
        } else {
            $data['check_previous_parts'] = false;
            $data['access_after_day'] = null;
        }

        $lessonsCount = TextLesson::where('webinar_id', $data['webinar_id'])->count();

        $webinar = Webinar::where('id', $data['webinar_id'])->first();

        if (!empty($webinar)) {
            $textLesson = TextLesson::create([
                'creator_id' => $webinar->creator_id,
                'webinar_id' => $data['webinar_id'],
                'chapter_id' => $data['chapter_id'] ?? null,
                'image' => $data['image'],
                'study_time' => $data['study_time'],
                'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
                'show_after_days' => (
                    (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
                    (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
                ) ? (int) $data['show_after_days'] : 0,
                'accessibility' => $data['accessibility'],
                'order' => $lessonsCount + 1,
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? TextLesson::$Active : TextLesson::$Inactive,
                'created_at' => time(),
            ]);

            if ($textLesson) {
                TextLessonTranslation::updateOrCreate([
                    'text_lesson_id' => $textLesson->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'content' => $data['content'],
                ]);


                if (!empty($data['attachments'])) {
                    $attachments = $data['attachments'];
                    $this->saveAttachments($textLesson, $attachments);
                }

                if (!empty($textLesson->chapter_id)) {
                    WebinarChapterItem::makeItem($webinar->creator_id, $textLesson->chapter_id, $textLesson->id, WebinarChapterItem::$chapterTextLesson);
                }
            }

            return response()->json([
                'code' => 200,
            ], 200);
        }

        return response()->json([], 422);
    }
    // public function update(Request $request, $id)
    // {
    //     $user = auth()->user();
    //     $data = $request->get('ajax')[$id];


    //     $validator = Validator::make($data, [
    //         'webinar_id' => 'required',
    //         'chapter_id' => 'required',
    //         'title' => 'required',
    //         'study_time' => 'required|numeric',
    //         'image' => 'required',
    //         'accessibility' => 'required|' . Rule::in(File::$accessibility),
    //         'summary' => 'required',
    //         'content' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response([
    //             'code' => 422,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $webinar = Webinar::find($data['webinar_id']);

    //     if (!empty($webinar) and $webinar->canAccess($user)) {

    //         $textLesson = TextLesson::where('id', $id)
    //         ->where('creator_id', $user->id)
    //         ->first();

    //         if (!empty($textLesson)) {
    //             $textLesson->update([
    //             'image' => $data['image'],
    //             'study_time' => $data['study_time'],
    //             'accessibility' => $data['accessibility'],
    //             'status' => (!empty($data['status']) and $data['status'] == 'on') ? TextLesson::$Active : TextLesson::$Inactive,
    //             'updated_at' => time(),
    //             'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
    //             'show_after_days' => (
    //                 (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
    //                 (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
    //                 ) ? (int) $data['show_after_days'] : 0,
    //             ]);

    //             TextLessonTranslation::updateOrCreate([
    //                 'text_lesson_id' => $textLesson->id,
    //                 'locale' => mb_strtolower($data['locale']),
    //             ], [
    //                 'title' => $data['title'],
    //                 'summary' => $data['summary'],
    //                 'content' => $data['content'],
    //             ]);

    //             $textLesson->attachments()->delete();

    //             if (!empty($data['attachments'])) {
    //                 $attachments = $data['attachments'];
    //                 $this->saveAttachments($textLesson, $attachments);
    //             }

    //             if($request->has("audio-file")) {
    //                 // $newFileName = moveAudioFile($request, $user);
    //                 // $audio = AudioAttachment::where("text_lesson_id", $textLesson->id)->first();
    //                 // if($audio) {
    //                 //     $audio->update([
    //                     //         "attached_by" => $user->id,
    //                     //         "webinar_id" => $data["webinar_id"],
    //                     //         "text_lesson_id" => $textLesson->id,
    //                     //         "file_name" => $newFileName,
    //                     //     ]);
    //                     // } else {
    //                         //     AudioAttachment::create([
    //                 //         "attached_by" => $user->id,
    //                 //         "webinar_id" => $data["webinar_id"],
    //                 //         "text_lesson_id" => $textLesson->id,
    //                 //         "file_name" => $newFileName,
    //                 //     ]);
    //                 // }
    //                 $this->updateAudio($request, $user->id, $data["webinar_id"], $textLesson->id);

    //             }
    //             //dd('habula');

    //             return response()->json([
    //                 'code' => 200,
    //             ], 200);
    //         }
    //     }

    //     abort(403);
    // }
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if ($request->ajax()) {
            $data = $request->get('ajax')[$id];

        } else {

            $data = $request->all();
        }

        $validator = Validator::make($data, [
            'webinar_id' => 'required',
            'title' => 'required',
            'study_time' => 'required|numeric',
            'image' => 'required',
            'accessibility' => 'required|' . Rule::in(File::$accessibility),
            'summary' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        //updating the drip feed
        if ($data['show_after_days'] == 0) {
            $data['drip_feed'] = 0;
        } else {
            $data['drip_feed'] = 1;
        }
        $textLesson = TextLesson::where('id', $id)
            ->first();

        if (!empty($textLesson)) {
            $textLesson->update([
                'chapter_id' => $data['chapter_id'] ?? null,
                'image' => $data['image'],
                'study_time' => $data['study_time'],
                'accessibility' => $data['accessibility'],
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? TextLesson::$Active : TextLesson::$Inactive,
                'updated_at' => time(),
                'drip_feed' => (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) ? 1 : 0,
                'show_after_days' => (
                    (!empty($data['drip_feed']) && (int) $data['drip_feed'] === 1) &&
                    (!empty($data['show_after_days']) && (int) $data['show_after_days'] > 0)
                ) ? (int) $data['show_after_days'] : 0,
            ]);

            $content = $data['content'];
            $dom = new \DomDocument();

            // $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $dom->loadHtml(
                $content,
                LIBXML_HTML_NOIMPLIED | # Make sure no extra BODY
                    LIBXML_HTML_NODEFDTD |  # or DOCTYPE is created
                    LIBXML_NOERROR |        # Suppress any errors
                    LIBXML_NOWARNING        # or warnings about prefixes.
            );
            //saving instantly once to get rid of prefix tags that are introduced from copying from Word
            $content = $dom->saveHtml();

            //load content again (TBD)
            //dd('here');

            $imageFile = $dom->getElementsByTagName('img');

            foreach ($imageFile as $item => $image) {
                $base64_data = $image->getAttribute('src');
                //only proceed with image conversion and save attempt if base64 encoded pattern is encountered.
                if (strpos($base64_data, ';base64,') !== false) {
                    list($type, $base64_data) = explode(';', $base64_data);
                    list(, $base64_data)      = explode(',', $base64_data);

                    //Image extension based on image type
                    list(, $imageExtension) = explode('/', $type);
                    $imageExtension = "." . $imageExtension;

                    $imageData = base64_decode($base64_data);

                    //create path if it doesn't exist
                    $subPath = '/store/webinars/' . $id;
                    $path = public_path() . $subPath;
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $image_name = time() . $item . $imageExtension;
                    $path .= '/' . $image_name;
                    file_put_contents($path, $imageData);

                    $image->removeAttribute('src');
                    $image->setAttribute('src', $subPath . '/' . $image_name);
                }
            }
            $content = $dom->saveHTML();

            TextLessonTranslation::updateOrCreate([
                'text_lesson_id' => $textLesson->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'summary' => $data['summary'],
                // 'content' => $data['content'],
                'content' => $content,
            ]);

            $textLesson->attachments()->delete();

            if (!empty($data['attachments'])) {
                $attachments = $data['attachments'];
                $this->saveAttachments($textLesson, $attachments);
            }

            if($request->has("audio-file")) {
                $this->updateAudio($request, $user->id, $data["webinar_id"], $textLesson->id);
            }

            removeContentLocale();

            return response()->json([
                'code' => 200,
            ], 200);
        }

        removeContentLocale();

        return response()->json([], 422);
    }
    public function destroy($id)
    {
        $textLesson = TextLesson::where('id', $id)->first();

        if (!empty($textLesson)) {

            if ((blank($textLesson->quizzes) && count($textLesson->quizzes) == 0) && (blank($textLesson->attachments) && count($textLesson->attachments) == 0)){

                WebinarChapterItem::where('user_id', $textLesson->creator_id)
                    ->where('item_id', $textLesson->id)
                    ->where('type', WebinarChapterItem::$chapterTextLesson)
                    ->delete();

                $textLesson->delete();

                return response()->json([
                    'code' => 200,
                ], 200);

            } else {
                return response()->json([
                    'code' => 422,
                    'message' => 'Detach the attachments first.'
                ], 422);
            }

        }

    }

    public function duplicate($id)
    {
        $id = (int) $id;
        //Fallback value as failure
        $toastData['title'] = trans('public.request_failed');
        $toastData['msg'] = $statusMessage = trans('admin/main.content_duplicate_failure');
        $toastData['status'] = $status = 'error';

        if ($id > 0 && $this->duplicateCourseLesson($id)) {
            $status = 'success';
            $toastData['title'] = trans('public.request_success');
            $toastData['msg'] = $statusMessage = trans('admin/main.content_duplicate_success');
            $toastData['status'] = $status = 'success';
        }
        return redirect()->back()->with([
            $status => $statusMessage,
            'toast' => $toastData,
        ]);
        abort(403);

    }

    private function saveAttachments($textLesson, $attachments)
    {
        if (!empty($attachments)) {

            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }

            foreach ($attachments as $attachment_id) {
                if (!empty($attachment_id)) {
                    TextLessonAttachment::create([
                        'text_lesson_id' => $textLesson->id,
                        'file_id' => $attachment_id,
                        'created_at' => time(),
                    ]);
                }
            }
        }
    }

    public function getAllLessonsByChapterId($id)
    {
        $user = auth()->user();
        $response = [
            'success' => 0,
            'status' => 'error'
        ];
        if ($user->isTeacher())
        {
            $id = (int)$id;
            if ($id > 0) {
                $textLessons = TextLesson::where('chapter_id', $id)->get();
                if ($textLessons->count() > 0) {
                    $response['success'] = 1;
                    $response['status'] = 'success';
                    $response['data'] = $textLessons->toArray();
                }
            }
            return response()->json($response, 200);
        }
        return response()->json($response, 403);
    }
}

