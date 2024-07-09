<?php

namespace App\View\Components\Panel;

use App\Models\QuizzesResult;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class QuizLink extends Component
{
    public $quiz;
    public $released;
    public $isActive;
    public $nested;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($quiz)
    {
        $this->quiz = $quiz;
        $this->released = $quiz->isReleased();
        $this->isActive = $this->isLinkActive();
        $this->nested = (!empty($this->quiz) && !empty($this->quiz->text_lesson_id));
    }

    public function isLinkActive()
    {
        $isQuizStartPage = route('panel.quizzes.start', ['id' => $this->quiz->id]) == url()->current();
        $isQuizStatusPage = false;
        if (Route::currentRouteName() === 'quiz_status') {
            $parameters = Route::current()->parameters();
            $quizResultId = $parameters['quizResultId'];
            $quizResult = QuizzesResult::find($quizResultId);
            $isQuizStatusPage = $quizResult->quiz_id == $this->quiz->id;
        }
        return ($isQuizStartPage || $isQuizStatusPage);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {

        return view('components.panel.quiz-link');
    }
}
