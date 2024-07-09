<?php

namespace App\View\Components\Course;

use App\Models\Webinar;
use Illuminate\View\Component;

class LessonNavigationButtons extends Component
{
    public $course;
    public $next;
    public $previous;
    public $showFullScreenButton;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(int $webinarId ,$next = null, $previous = null, $showFullScreenButton = true)
    {
        $webinar = Webinar::find($webinarId);
        $this->course = $webinar;
        $this->next = $next;
        $this->previous = $previous;
        $this->showFullScreenButton = $showFullScreenButton;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course.lesson-navigation-buttons');
    }
}
