<?php

namespace App\View\Components\CoursePreview;

use Illuminate\View\Component;

class TextLessonButtons extends Component
{
    public $course;
    public $textLesson;
    public $authUser;
    public $requestedUser;
    public $isAdmin;
    public $studentId = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($course, $textLesson, $requestedUser = null)
    {
        //
        $this->course = $course;
        $this->textLesson = $textLesson;
        $this->requestedUser = $requestedUser;
        $this->authUser = auth()->user();
        $this->isAdmin = $this->authUser->isAdmin();
        if ($this->authUser->isUser()) {
            $this->studentId = $this->authUser->id;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course-preview.text-lesson-buttons');
    }
}
