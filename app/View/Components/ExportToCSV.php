<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ExportToCSV extends Component
{
    public $btnText;
    public $url;
    public $query;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($btnText=null , $route = null ,  $query=null)
    {
        $this->btnText = $btnText;
        $this->url = $route;
        $this->query = $query;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.export-to-c-s-v');
    }
}
