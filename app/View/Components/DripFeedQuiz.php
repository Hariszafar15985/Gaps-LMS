<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DripFeedQuiz extends Component
{
    public $quiz;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($quiz = null)
    {
        //
        $this->quiz = $quiz;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.drip-feed-quiz');
    }
}
