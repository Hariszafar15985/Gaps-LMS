<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CourseDropDown extends Component
{
    // public $error;
    public $webinar;
    public $currency;
    public $selected;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($webinar,$selected,$currency)
    {
        $this->webinar = $webinar;
        $this->selected = $selected;
        $this->currency = $currency;
        // $this->error = $error;
    }

    public function isSelected($option){

        return $option === $this->selected;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course-drop-down');
    }
}
