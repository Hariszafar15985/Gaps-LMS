<?php

namespace App\View\Components;

use App\Models\TextLesson;
use App\Models\Webinar;
use Illuminate\View\Component;

class QuizAttemptError extends Component
{
    public $error;
    // public $nextLessonId;
    // public $courseSlug;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($error)
    {
        // $this->courseSlug = $courseSlug;
        // $this->nextLessonId = $nextLessonId;
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.quiz-attempt-error');
    }
}
