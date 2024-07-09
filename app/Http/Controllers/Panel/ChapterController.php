<?php

namespace App\Http\Controllers\Panel;

use App\Models\File;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\Webinar;
use App\Models\AuditTrail;
use App\Models\TextLesson;
use Illuminate\Http\Request;
use App\Models\WebinarChapter;
use App\Models\QuizzesQuestion;
use Illuminate\Validation\Rule;
use App\Models\WebinarAssignment;
use App\Models\WebinarChapterItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TextLessonAttachment;
use App\Traits\CourseDuplicatorTrait;
use App\Models\QuizzesQuestionsAnswer;
use Illuminate\Support\Facades\Validator;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\TextLessonTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Traits\WebinarChapterTrait;

class ChapterController extends Controller
{
    use CourseDuplicatorTrait;
    // following WebinarChapterTrait is used to keep the common methods for both admin and panel side
    use WebinarChapterTrait;

    public function getChapter(Request $request, $id)
    {
        $user = auth()->user();

        $chapter = WebinarChapter::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        $locale = $request->get('locale', app()->getLocale());

        if (!empty($chapter)) {
            foreach ($chapter->translatedAttributes as $attribute) {
                try {
                    $chapter->$attribute = $chapter->translate(mb_strtolower($locale))->$attribute;
                } catch (\Exception $e) {
                    $chapter->$attribute = null;
                }
            }

            $data = [
                'chapter' => $chapter
            ];

            return response()->json($data, 200);
        }

        abort(403);
    }

    public function getAllByWebinarId($webinar_id)
    {
        $user = auth()->user();

        $webinar = Webinar::find($webinar_id);

        if (!empty($webinar) and $webinar->canAccess($user)) {
            $data = [
                'chapters' => $webinar->chapters->where('status', WebinarChapter::$chapterActive),
            ];

            return response()->json($data, 200);
        }

        abort(403);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->get('ajax')['chapter'];

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

        $webinar = Webinar::find($data['webinar_id']);

        if (!empty($webinar) and $webinar->canAccess($user)) {
            $status = (!empty($data['status']) and $data['status'] == 'on') ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;

            $chapter = WebinarChapter::create([
                'user_id' => $user->id,
                'webinar_id' => $webinar->id,
                'type' => $data['type'],
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

        abort(403);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $chapter = WebinarChapter::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($chapter)) {
            $data = $request->get('ajax')['chapter'];

            $validator = Validator::make($data, [
                'webinar_id' => 'required',
                // 'type' => 'required|' . Rule::in(WebinarChapter::$chapterTypes),
                'title' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $status = (!empty($data['status']) and $data['status'] == 'on') ? WebinarChapter::$chapterActive : WebinarChapter::$chapterInactive;

            $chapter->update([
                'status' => $status,
            ]);

            WebinarChapterTranslation::updateOrCreate([
                'webinar_chapter_id' => $chapter->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        abort(403);
    }

    public function destroy(Request $request, $id)
    {
        // $this->authorize('admin_webinars_edit');

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

    public function getTextLesson($chapterId)
    {
        $chapter = WebinarChapter::find($chapterId);
        $textLessons = $chapter->TextLessons;
        return response()->json([
            'data' => $textLessons
        ], 200);
    }
}
