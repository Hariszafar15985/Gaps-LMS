<?php

namespace App\View\Components\Course;

use App\Helpers\WebinarHelper;
use App\Models\Webinar;
use App\Models\WebinarChapter;
use Illuminate\View\Component;
use App\Models\Quiz;

class Sidebar extends Component
{
    public $textLessonChapters;
    public $course;
    public $courseUrl;
    public $currentUrl;
    public $quizPage = false;
    public $dropDownListing = false;
    public $quizLessonId = null;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(int $webinarId, $quizPage = false, $quizId = null, $dropDownListing = false)
    {
        $user = auth()->user();
        $webinar = Webinar::find($webinarId);
        $webinarHelper = new WebinarHelper();
        $this->course = $webinarHelper->getCourseDataBySlug($webinar->slug, $user);
        $this->textLessonChapters = $this->course->chapters->where('type', WebinarChapter::$chapterTextLesson);
        $this->quizPage = $quizPage;
        $this->currentUrl = $this->prepareUrl($quizId);
        $this->courseUrl = $this->getCourseUrl();
        $this->course = Webinar::find($webinarId);
        if($dropDownListing == true){
            $this->dropDownListing = true;
        }
        if(!empty($quizId)){
            $quiz = Quiz::find($quizId);
            $this->quizLessonId = $quiz->text_lesson_id;
            if(empty($this->quizLessonId)) {
                $this->quizLessonId = $this->getQuizLessonId($quiz);
            }
        }
    }

    /**
     * Getting the lesson to which quiz attached or the previous lesson
     *
     * @param object $quiz
     * @return int
     */
    public function getQuizLessonId(object $quiz) {
        $webinarHelper = new WebinarHelper();
        $previousLesson = $webinarHelper->getPreviousContent($quiz->id, Webinar::$quiz);
        $contentType = $previousLesson['content_type'] ?? null;
        $content = $previousLesson['content'] ?? null;

        while ($contentType === Webinar::$quiz && !empty($previousLesson)) {
            $previousLesson = $webinarHelper->getPreviousContent($content->id, Webinar::$quiz);
            $contentType = $previousLesson['content_type'] ?? null;
            $content = $previousLesson['content'] ?? null;
        }

        return $content ? $content->id : null;
    }

    public function getCourseUrl()
    {
        $webinarId = null;
        foreach ($this->textLessonChapters as $textLessonChapter) {
            foreach ($textLessonChapter->textLessons as $lesson) {

                if (!empty($lesson->webinar_id)) {
                    $webinarId = (int) $lesson->webinar_id;
                    break;
                }
            }
            if (!empty($webinarId)) {
                break;
            }
        }
        $webinar = Webinar::find($webinarId);
        if (!empty($webinar)) {
            return $webinar->getUrl();
        }
        return null;
    }

    public function prepareUrl($quizId = null)
    {
        if ($this->quizPage) {
            return route('panel.quizzes.start', ['id' => $quizId]);
        } else {
            //in case of a lesson
            return url()->current();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.course.sidebar');
    }
}
