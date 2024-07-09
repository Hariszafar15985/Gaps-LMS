<?php

namespace App\Traits;

use App\Models\AuditTrail;
use App\Models\File;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\TextLesson;
use App\Models\TextLessonAttachment;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\WebinarChapter;
use Illuminate\Support\Facades\DB;

trait CourseDuplicatorTrait
{
    public function duplicateCourseChapter($id)
    {
        $user = auth()->user();

        $oldChapterIds = [$id];
            
        $chapters = WebinarChapter::where('id', $id)->get();
        if (!empty($chapters)) {
            if ($chapters->count() > 0) {
                $creationTime = time();
                $chapterMappings = [];
                $webinarId = $chapterOrder = null;
                foreach($chapters as $chapter) {
                    $cloneData = $chapter->toArray();
                    $webinarId = $cloneData['webinar_id'];
                    $chapterOrder = $cloneData['order'];
                    $cloneData['created_at'] = $creationTime;
                    unset($cloneData['id']);
                    $cloneData['title'] .= ' - Copy';
                    $clonedChapter = WebinarChapter::Create($cloneData);
                    $chapterMappings[$chapter->id] = $clonedChapter->id;
                }
    
                //Now let's insert the translations
                $oldChapterIds = array_keys($chapterMappings);
    
                //Now that chapters are inserted, let's add files and text lessons
                if (isset($oldChapterIds) && count($oldChapterIds) > 0) {

                    //File Attachments
                    $files = File::whereIn('chapter_id', $oldChapterIds)->get();
                    if ($files->count() > 0) {
                        $fileMappings = [];
                        foreach($files as $file) {
                            $newFile = $file->toArray();
                            unset($newFile['id']);
                            $oldChapterId = $newFile['chapter_id'];
                            $newFile['chapter_id'] = $chapterMappings[$oldChapterId];
                            $insertedFile = File::create($newFile);
                            $fileMappings[$file->id] = $insertedFile->id;
                        }
                        //File Translations
                        $oldFileIds = array_keys($fileMappings);
                        $oldFileTranslations = FileTranslation::whereIn('file_id', $oldFileIds)->get();
                        if ($oldFileTranslations->count() > 0) {
                            $fileTranslationsData = [];
                            foreach($oldFileTranslations as $oldFileTranslation) {
                                $fileTranslation = $oldFileTranslation->toArray();
                                unset($fileTranslation['id']);
                                $oldFileId = $fileTranslation['file_id'];
                                $fileTranslation['file_id'] = $fileMappings[$oldFileId];
                                $fileTranslationsData[] = $fileTranslation;
                            }
                            FileTranslation::Insert($fileTranslationsData);
                        }
                    }

                    //Text Lessons
                    $textLessons = TextLesson::whereIn('chapter_id', $oldChapterIds)->get();
                    if ($textLessons->count() > 0) {
                        $textLessonMappings = [];
                        foreach($textLessons as $textlesson) {
                            $newTextLesson = $textlesson->toArray();
                            unset($newTextLesson['id']);
                            $newTextLesson['created_at'] = $creationTime;
                            $newTextLesson['updated_at'] = null;
                            $newTextLesson['chapter_id'] = $chapterMappings[$textlesson->chapter_id];
                            $newTextLesson['title'] .= ' - Copy';
                            $clonedTextLesson = TextLesson::Create($newTextLesson);
                            $textLessonMappings[$textlesson->id] = $clonedTextLesson->id;
                        }
                        $oldTextLessonIds = array_keys($textLessonMappings);
    
                        //Text Lesson Attachments
                        if ($files->count() > 0) {
    
                            $lessonAttachments = TextLessonAttachment::whereIn('text_lesson_id', $oldChapterIds)->get();
                            if ($lessonAttachments->count() > 0) {
                                $textLessonAttachmentData = [];
                                foreach($lessonAttachments as $lessonAttachment) {
                                    $attachment = $lessonAttachment->toArray();
                                    unset($attachment['id']);
                                    $attachment['created_at'] = $creationTime;
                                    $oldTextLessonId = $attachment['text_lesson_id'];
                                    $attachment['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
                                    $oldFileId = $attachment['file_id'];
                                    $attachment['file_id'] = $fileMappings[$oldFileId];
                                    $textLessonAttachmentData[] = $attachment;
                                }
                                TextLessonAttachment::Insert($textLessonAttachmentData);
                            }
                        }
                        
                    }
                }
    
                //Clone Quizzes
                $oldQuizzes = Quiz::whereIn('chapter_id', $oldChapterIds)->get();
                if ($oldQuizzes->count() > 0) {
                    $quizMappings = [];
                    foreach($oldQuizzes as $oldQuiz) {
                        $quiz = $oldQuiz->toArray();
                        unset($quiz['id']);
                        $quiz['created_at'] = $creationTime;
                        $quiz['updated_at'] = null;
                        $oldChapterId = $quiz['chapter_id'];
                        $quiz['chapter_id'] = $chapterMappings[$oldChapterId];
                        $newQuiz = Quiz::Create($quiz);
                        $quizMappings[$oldQuiz->id] = $newQuiz->id; 
                    }
                    $oldQuizIds = array_keys($quizMappings);
    
                    //Quiz Translations
                    $oldQuizTranslations = QuizTranslation::whereIn('quiz_id', $oldQuizIds)->get();
                    if ($oldQuizTranslations->count() > 0) {
                        $quizTranslationData = [];
                        foreach($oldQuizTranslations as $oldQuizTranslation) {
                            $quizTranslation = $oldQuizTranslation->toArray();
                            unset($quizTranslation['id']);
                            $oldQuizId = $quizTranslation['quiz_id'];
                            $quizTranslation['quiz_id'] = $quizMappings[$oldQuizId];
                            $quizTranslationData[] = $quizTranslation;
                        }
                        QuizTranslation::Insert($quizTranslationData);
                    }
    
                    //Quiz Questions
                    $oldQuizQuestions = QuizzesQuestion::whereIn('quiz_id', $oldQuizIds)->get();
                    if ($oldQuizQuestions->count() > 0) {
                        $quizQuestionMappings = [];
                        foreach($oldQuizQuestions as $oldQuizQuestion) {
                            $quizQuestion = $oldQuizQuestion->toArray();
                            unset($quizQuestion['id']);
                            $oldQuizId = $quizQuestion['quiz_id'];
                            $quizQuestion['quiz_id'] = $quizMappings[$oldQuizId];
                            $quizQuestion['created_at'] = $creationTime;
                            $quizQuestion['updated_at'] = null;
                            $newQuizQuestion = QuizzesQuestion::Create($quizQuestion);
                            $quizQuestionMappings[$oldQuizQuestion->id] = $newQuizQuestion->id; 
                        }
    
                        //Quiz Question Translations
                        $oldQuizQuestionIds = array_keys($quizQuestionMappings);
                        $oldQuizQuestionTranslations = QuizzesQuestionTranslation::whereIn('quizzes_question_id', $oldQuizQuestionIds)->get();
                        if ($oldQuizQuestionTranslations->count() > 0) {
                            $questionTranslationsData = [];
                            foreach($oldQuizQuestionTranslations as $oldQuizQuestionTranslation) {
                                $questionTranslation = $oldQuizQuestionTranslation->toArray();
                                unset($questionTranslation['id']);
                                $oldQuizQuestionId = $questionTranslation['quizzes_question_id'];
                                $questionTranslation['quizzes_question_id'] = $quizQuestionMappings[$oldQuizQuestionId];
                                $questionTranslationsData[] = $questionTranslation;
                            }
                            QuizzesQuestionTranslation::Insert($questionTranslationsData);
                        }
    
                        //Quiz Question Answers
                        $oldQuestionAnswers = QuizzesQuestionsAnswer::whereIn('question_id', $oldQuizQuestionIds)->get();
                        if ($oldQuestionAnswers->count() > 0) {
                            $questionAnswerMappings = [];
                            foreach($oldQuestionAnswers as $oldQuestionAnswer) {
                                $questionAnswer = $oldQuestionAnswer->toArray();
                                unset($questionAnswer['id']);
                                $oldQuestionId = $questionAnswer['question_id'];
                                $questionAnswer['question_id'] = $quizQuestionMappings[$oldQuestionId];
                                $questionAnswer['created_at'] = $creationTime;
                                $quizQuestionAnswer = QuizzesQuestionsAnswer::Create($questionAnswer);
                                $questionAnswerMappings[$oldQuestionAnswer->question_id] = $quizQuestionAnswer->id;
                            }
    
                            //Quiz Question Answer Translations
                            $oldQuestionAnswerIds = array_keys($questionAnswerMappings);
                            $oldAnswerTranslations = QuizzesQuestionsAnswerTranslation::whereIn('quizzes_questions_answer_id', $oldQuestionAnswerIds)->get();
                            if ($oldAnswerTranslations->count() > 0) {
                                $answerTranslationsData = [];
                                foreach ($oldAnswerTranslations as $oldAnswerTranslation) {
                                    $answerTranslation = $oldAnswerTranslation->toArray();
                                    unset($answerTranslation['id']);
                                    $oldQuestionAnswerId = $answerTranslation['quizzes_questions_answer_id'];
                                    $answerTranslation['quizzes_questions_answer_id'] = $questionAnswerMappings[$oldQuestionAnswerId];
                                    $answerTranslationsData[] = $answerTranslation;
                                }
                                QuizzesQuestionsAnswerTranslation::Insert($answerTranslationsData);
                            }
                        }
                    }
                }

                if (isset($chapterMappings) && count($chapterMappings) > 0) {
                    foreach ($chapterMappings as $oldId => $newId) {
                        //Audit Trail entry - course duplicated
                        $audit = new AuditTrail();
                        $audit->user_id = $user->id;
                        $audit->organ_id = $user->organ_id;
                        $audit->role_name = $user->role_name;
                        $audit->audit_type = AuditTrail::auditType['chapter_duplicated'];
                        $audit->added_by = $user->id;
                        $audit->description = "User {$user->full_name} ({$user->id}) cloned existing chapter ({$oldId}). New Chapter id: {$newId}";
                        $ip = null;
                        $ip = getClientIp();
                        $audit->ip = ip2long($ip);
                        $audit->save();
                    }
                    
                    //Increment the order of all the chapters of this webinar, beyond the received chapter id by 1
                    if (!empty($chapterOrder)) { //order of file chapters is null, so skip those
                        WebinarChapter::where('order', '>=', $chapterOrder)
                        ->where('id', '<>', $id)
                        ->update([
                            'order' =>  DB::raw('`order` + 1')
                        ]);
                    }

                    return true;
                }
            }
            return false; //for some reason the course chapter could not be duplicated
        }
    }

    public function duplicateCourseLesson($id) 
    {
        $user = auth()->user();

        $textLesson = TextLesson::where('id', $id)
            ->get();

        if (!empty($textLesson) && $textLesson->count() > 0) {
            $creationTime = time();
            //Adding files and text lessons
            $oldChapterIds = [];
            foreach($textLesson as $lesson) {
                $oldChapterIds[] = $lesson->chapter_id;
            }
            
            //File Attachments
            $files = TextLessonAttachment::where('text_lesson_id', $id)->get();
            //$files = File::whereIn('chapter_id', $oldChapterIds)->get();
            if ($files->count() > 0) {
                $fileMappings = [];
                foreach($files as $file) {
                    $newFile = $file->toArray();
                    unset($newFile['id']);
                    $oldChapterId = $newFile['chapter_id'];
                    //$newFile['chapter_id'] = $chapterMappings[$oldChapterId];
                    $insertedFile = File::create($newFile);
                    $fileMappings[$file->id] = $insertedFile->id;
                }
                //File Translations
                $oldFileIds = array_keys($fileMappings);
                $oldFileTranslations = FileTranslation::whereIn('file_id', $oldFileIds)->get();
                if ($oldFileTranslations->count() > 0) {
                    $fileTranslationsData = [];
                    foreach($oldFileTranslations as $oldFileTranslation) {
                        $fileTranslation = $oldFileTranslation->toArray();
                        unset($fileTranslation['id']);
                        $oldFileId = $fileTranslation['file_id'];
                        $fileTranslation['file_id'] = $fileMappings[$oldFileId];
                        $fileTranslationsData[] = $fileTranslation;
                    }
                    FileTranslation::Insert($fileTranslationsData);
                }
            }

            //Text Lessons
            //$textLessons = TextLesson::whereIn('chapter_id', $oldChapterIds)->get();
            if ($textLesson->count() > 0) {
                $textLessonMappings = [];
                foreach($textLesson as $textlesson) {
                    $newTextLesson = $textlesson->toArray();
                    $textLessonOrder = $textlesson->order;
                    unset($newTextLesson['id']);
                    $newTextLesson['title'] .= ' - Copy';
                    $newTextLesson['created_at'] = $creationTime;
                    $newTextLesson['updated_at'] = null;
                    $clonedTextLesson = TextLesson::Create($newTextLesson);
                    $textLessonMappings[$textlesson->id] = $clonedTextLesson->id;
                }
                $oldTextLessonIds = array_keys($textLessonMappings);

                //Text Lesson Attachments
                if ($files->count() > 0) {

                    $lessonAttachments = TextLessonAttachment::whereIn('text_lesson_id', $oldChapterIds)->get();
                    if ($lessonAttachments->count() > 0) {
                        $textLessonAttachmentData = [];
                        foreach($lessonAttachments as $lessonAttachment) {
                            $attachment = $lessonAttachment->toArray();
                            unset($attachment['id']);
                            $attachment['created_at'] = $creationTime;
                            $oldTextLessonId = $attachment['text_lesson_id'];
                            $attachment['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
                            $oldFileId = $attachment['file_id'];
                            $attachment['file_id'] = $fileMappings[$oldFileId];
                            $textLessonAttachmentData[] = $attachment;
                        }
                        TextLessonAttachment::Insert($textLessonAttachmentData);
                    }
                }

                if (isset($textLessonMappings) && count($textLessonMappings) > 0) {
                    foreach ($textLessonMappings as $oldId => $newId) {
                        //Audit Trail entry - course duplicated
                        $audit = new AuditTrail();
                        $audit->user_id = $user->id;
                        $audit->organ_id = $user->organ_id;
                        $audit->role_name = $user->role_name;
                        $audit->audit_type = AuditTrail::auditType['text_lesson_duplicated'];
                        $audit->added_by = $user->id;
                        $audit->description = "User {$user->full_name} ({$user->id}) cloned existing text lesson ({$oldId}). New Text Lesson id: {$newId}";
                        $ip = null;
                        $ip = getClientIp();
                        $audit->ip = ip2long($ip);
                        $audit->save();
                    }
                }
            
                //Increment the order of all the chapters of this webinar, beyond the received chapter id by 1
                if (!empty($textLessonOrder)) { //order of file chapters is null, so skip those
                    TextLesson::where('order', '>=', $textLessonOrder)
                    ->where('id', '<>', $id)
                    ->update([
                        'order' =>  DB::raw('`order` + 1')
                    ]);
                }
                return true;
            }
        }
        return false;
    }

    
}
