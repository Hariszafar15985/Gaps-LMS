<?php

use Illuminate\Database\Seeder;
use App\Models\Webinar;
use App\Models\WebinarChapterItem;

class WebinarChapterItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $webinars = Webinar::get();

        foreach ($webinars as $index => $webinar) {
            if (!blank($webinar->chapters)) {
                foreach ($webinar->chapters as $webChapter) {
                    // following arrays will get the records agains each chapter
                    if (!blank($webChapter->files)) {
                        $webChapterFiles = $webChapter->files->sortBy(function ($file) {
                            return [$file->order, $file->id];
                        });
                        foreach ($webChapterFiles as $fileIndex => $chapterFile) {

                            WebinarChapterItem::create([
                                'user_id' => $chapterFile->creator_id,
                                'chapter_id' => $webChapter->id,
                                'item_id' => $chapterFile->id,
                                'type' => 'file',
                                'created_at' => $chapterFile->created_at,
                                'order' => ++$fileIndex
                            ]);
                        }
                    }

                    if (!blank($webChapter->textLessons)) {
                        $webTextLessons = $webChapter->textLessons->sortBy(function ($lesson) {
                            return [$lesson->order, $lesson->id];
                        });
                        foreach ($webTextLessons as $lessonIndex => $chapterTextLesson) {
                            WebinarChapterItem::create([
                                'user_id' => $chapterTextLesson->creator_id,
                                'chapter_id' => $webChapter->id,
                                'item_id' => $chapterTextLesson->id,
                                'type' => 'text_lesson',
                                'created_at' => $chapterTextLesson->created_at,
                                'order' => ++$lessonIndex
                            ]);
                        }
                    }
                    if (!blank($webChapter->sessions)) {
                        $webSessions = $webChapter->sessions->sortBy(function ($session) {
                            return [$session->order, $session->id];
                        });
                        foreach ($webSessions as $sessionIndex => $chapterSession) {
                            WebinarChapterItem::create([
                                'user_id' => $chapterSession->creator_id,
                                'chapter_id' => $webChapter->id,
                                'item_id' => $chapterSession->id,
                                'type' => 'session',
                                'created_at' => $chapterSession->created_at,
                                'oredr' => ++$sessionIndex
                            ]);
                        }
                    }
                    if (!blank($webChapter->quizzes)) {
                        $webQuizzes= $webChapter->quizzes->sortBy(function ($quiz) {
                            return [$quiz->order, $quiz->id];
                        });
                        foreach ($webQuizzes as $QuizIndex => $chapterQuiz) {
                            WebinarChapterItem::create([
                                'user_id' => $chapterQuiz->creator_id,
                                'chapter_id' => $webChapter->id,
                                'item_id' => $chapterQuiz->id,
                                'type' => 'quiz',
                                'created_at' => $chapterQuiz->created_at,
                                'order' => ++$QuizIndex,
                            ]);
                        }
                    }
                    if (!blank($webChapter->assignments)) {
                        $webAssignments= $webChapter->assignments->sortBy(function ($assignment) {
                            return [$assignment->order, $assignment->id];
                        });
                        foreach ($webAssignments as $assignmentIndex => $chapterAssignment) {
                            WebinarChapterItem::create([
                                'user_id' => $chapterAssignment->creator_id,
                                'chapter_id' => $webChapter->id,
                                'item_id' => $chapterAssignment->id,
                                'type' => 'assignment',
                                'created_at' => $chapterAssignment->created_at,
                                'order' => ++$assignmentIndex
                            ]);
                        }
                    }
                }
            }
        }
    }
}
