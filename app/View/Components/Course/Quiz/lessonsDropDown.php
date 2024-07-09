<?php

namespace App\View\Components\Course\Quiz;

use Illuminate\View\Component;
use App\Helpers\WebinarHelper;
use App\Models\Webinar;

class lessonsDropDown extends Component
{
    public $textLessonChapters = null;
    public $quizLessonId = null;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($textLessonChapters = null, $quizLessonId = null)
    {
        $this->textLessonChapters = $textLessonChapters;
        $this->quizLessonId = $quizLessonId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course.quiz.lessons-drop-down');
    }
}
