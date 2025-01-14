<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quiz;
use App\Models\Session;
use App\Models\Webinar;
use App\Models\Api\File;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\WebinarChapter;
use Illuminate\Validation\Rule;
use App\Models\WebinarAssignment;
use App\Models\WebinarChapterItem;
use App\Http\Controllers\Controller;
use App\Traits\CourseDuplicatorTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Translation\WebinarChapterTranslation;
use App\Traits\WebinarChapterTrait;

class ChapterController extends Controller
{
    use CourseDuplicatorTrait;
    // following WebinarChapterTrait is used to keep the common methods for both admin and panel side
    use WebinarChapterTrait;

    public function store(Request $request)
    {
        $this->authorize('admin_webinars_edit');
        $data = $request->get('ajax')['chapter'];
        $validator = Validator::make($data, [
            'webinar_id' => 'required',
            // 'type' => 'required|' . Rule::in(WebinarChapter::$chapterTypes), (commented by the rocket lms in this update)
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }
        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::where('id', $data['webinar_id'])->first();
            if (!empty($webinar)) {
                $teacher = $webinar->creator;
                $status = (!empty($data['status']) and $data['status'] == 'on') ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;

                $chapter = WebinarChapter::create([
                    'user_id' => $teacher->id,
                    'webinar_id' => $webinar->id,
                    // 'type' => $webinar->type,
                    'title' => $data['title'],
                    'status' => $status,
                    'created_at' => time(),
                ]);

                if (!empty($chapter)) {
                    WebinarChapterTranslation::updateOrCreate([
                        'webinar_chapter_id' => $chapter->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                    ]);
                }


                return response()->json([
                    'code' => 200,
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $chapter = WebinarChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $locale = $request->get('locale', app()->getLocale());
            if (empty($locale)) {
                $locale = app()->getLocale();
            }
            storeContentLocale($locale, $chapter->getTable(), $chapter->id);

            $chapter->title = $chapter->getTitleAttribute();
            $chapter->locale = mb_strtoupper($locale);

            return response()->json([
                'chapter' => $chapter
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $validator = Validator::make($data, [
            'webinar_id' => 'required',
            'type' => 'required|' . Rule::in(WebinarChapter::$chapterTypes),
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $chapter = WebinarChapter::where('id', $id)->first();

        if (!empty($chapter)) {
            $webinar = Webinar::where('id', $data['webinar_id'])->first();

            if (!empty($webinar)) {
                $status = (!empty($data['status']) and $data['status'] == 'on') ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;

                $chapter->update([
                    'type' => $data['type'],
                    'status' => $status,
                ]);

                if (!empty($chapter)) {
                    WebinarChapterTranslation::updateOrCreate([
                        'webinar_chapter_id' => $chapter->id,
                        'locale' => mb_strtolower($data['locale']),
                    ], [
                        'title' => $data['title'],
                    ]);
                }

                removeContentLocale();

                return response()->json([
                    'code' => 200,
                ], 200);
            }
        }

        removeContentLocale();

        return response()->json([], 422);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $chapter = WebinarChapter::where('id', $id)->first();
        $chapterItems = WebinarChapterItem::where('chapter_id',$chapter->id)->get();
        if ((!empty($chapter) && blank($chapter->textLessons)) && (blank($chapter->quizzes) && blank($chapter->sessions)) && (blank($chapter->session) && blank($chapter->assignments) && (blank($chapterItems) && count($chapterItems) <= 0))) {
            $chapter->delete();
            return response()->json([
                'code' => 200,
            ], 200);
        }
        return response()->json([
            'code' => 422,
            'message' => 'First Delete this chapter Items.'
        ], 422);
    }

    public function duplicate($id)
    {
        $id = (int) $id;
        //Fallback value as failure
        $toastData['title'] = trans('public.request_failed');
        $toastData['msg'] = $statusMessage = trans('admin/main.content_duplicate_failure');
        $toastData['status'] = $status = 'error';

        if ($id > 0 && $this->duplicateCourseChapter($id)) {
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
}
