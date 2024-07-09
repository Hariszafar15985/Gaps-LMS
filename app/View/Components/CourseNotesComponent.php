<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\CourseNotes;

class CourseNotesComponent extends Component
{
    public $textLesson;
    public $file; //this variable is defined to keep the record of file notes if the course item is not a text lesson
    public $courseNotes;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($textLesson = null, $file = null)
    {
        $notes = null;
        if ($textLesson) {
            $this->textLesson = $textLesson;
            $user = auth()->user();
            $user ? $notes = $this->getNotes($user->id, $textLesson->id, $textLesson->webinar_id,'text_lesson') : null;
        }

        if ($file) {
            $this->file = $file;
            $user = auth()->user();
            $user ? $notes = $this->getNotes($user->id, $file->id, $file->webinar_id,'file') : null;
        }
        // dd($file->id);
        $this->courseNotes = $notes;
    }

    /**
     * This method returns notes of student against a spacific lesson, webinar or all
     * notes of that spacific
     *
     * @param int $userId
     * @param int $lessonId
     * @param int $webinarId
     * @return object notes of the student
     */
    public function getNotes($userId, $itemId , $webinarId , $type)
    {
        $notes = null;
        if ($type == 'text_lesson') {
            $notes = CourseNotes::where([
                "user_id" => $userId,
                "lesson_id" => $itemId,
                "webinar_id" => $webinarId,
            ])->first();
        }

        if ($type == 'file') {
            $notes = CourseNotes::where([
                "user_id" => $userId,
                "file_id" => $itemId,
                "webinar_id" => $webinarId,
            ])->first();
        }
        return $notes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course-notes-component');
    }
}
