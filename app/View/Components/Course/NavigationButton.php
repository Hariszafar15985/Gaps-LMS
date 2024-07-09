<?php

namespace App\View\Components\Course;

use Illuminate\View\Component;

class NavigationButton extends Component
{
    public $params = null;
    public $linkTarget = null;
    public $addClass = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($params , string $linkTarget = null, $addClass = null)
    {
        if (!empty($params)) {
            $this->params = $params;
        }
        if(!empty($linkTarget)) {
            $this->linkTarget = $this->linkTargetValueValidation($linkTarget) ? $linkTarget : null;
        }
        if(!empty($addClass)) {
            $this->addClass = $addClass;
        }
    }

    /**
     * Method to verify that the $linkTarget parameter has a valid value,
     * according to HTML5 for the anchor tag
     * Possible Values:
     * _blank: It opens the link in a new window.
     * _self: It is the default value. It opens the linked document in the same frame.
     * _parent: It opens the linked document in the parent frameset.
     * _top: It opens the linked document in the full body of the window.
     * framename: It opens the linked document in the named frame.
     *
     * @param string $linkTarget The 'target' attribute of the href link
     *
     * @return boolean
     */
    public function linkTargetValueValidation(string $linkTarget = null)
    {
        // Possible Values
        $validValues = ['_blank', '_self', '_parent', '_top', 'framename'];
        return (!empty($linkTarget) && in_array($linkTarget, $validValues));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course.navigation-button');
    }
}
