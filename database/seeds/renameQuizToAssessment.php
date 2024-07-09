<?php

use App\Models\Notification;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class renameQuizToAssessment extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Renaming in NotificaitonTemplate
        $notificationTemplateRecords = NotificationTemplate::where('title', 'like', '%quiz%')
                            ->orWhereLike('template', 'like', '%quiz%');
        if ($notificationTemplateRecords && count($notificationTemplateRecords)) {
            foreach($notificationTemplateRecords as $record) {
                //Title
                //UpperCase rename
                $record->title = str_replace('Quizzes', 'Assessments', $record->title);
                $record->title = str_replace('Quiz', 'Assessment', $record->title);
                //LowerCase rename
                $record->title = str_replace('quizzes', 'assessments', $record->title);
                $record->title = str_replace('quiz', 'assessment', $record->title);

                //Description
                //UpperCase replace - Plural
                $record->template = str_replace(' Quizzes ', ' Assessments ', $record->template);
                $record->template = str_replace('Quizzes ', 'Assessments ', $record->template);
                $record->template = str_replace(' Quizzes', ' Assessments', $record->template);
                //LowerCase replace - Plural
                $record->template = str_replace(' quizzes ', ' assessments ', $record->template);
                $record->template = str_replace('quizzes ', 'assessments ', $record->template);
                $record->template = str_replace(' quizzes', ' assessments', $record->template);
                
                //UpperCase replace - Singular
                $record->template = str_replace(' Quiz ', ' Assessment ', $record->template);
                $record->template = str_replace('Quiz ', 'Assessment ', $record->template);
                $record->template = str_replace(' Quiz', ' Assessment', $record->template);
                //LowerCase replace - Singular
                $record->template = str_replace(' quiz ', ' assessment ', $record->template);
                $record->template = str_replace('quiz ', 'assessment ', $record->template);
                $record->template = str_replace(' quiz', ' assessment', $record->template);
                
                $record->save();
            }
        }

        //Renaming in Sent Notifications
        $notifications = Notification::where('title', 'like', '%quiz%')
                            ->orWhereLike('message', 'like', '%quiz%');
        if ($notifications && count($notifications) > 0) {
            foreach($notifications as $record) {
                //Title
                $record->title = str_replace('Quizzes', 'Assessments', $record->title);
                $record->title = str_replace('Quiz', 'Assessment', $record->title);
                //LowerCase rename
                $record->title = str_replace('quizzes', 'assessments', $record->title);
                $record->title = str_replace('quiz', 'assessment', $record->title);

                //Message
                //UpperCase replace - Plural
                $record->message = str_replace(' Quizzes ', ' Assessments ', $record->message);
                $record->message = str_replace('Quizzes ', 'Assessments ', $record->message);
                $record->message = str_replace(' Quizzes', ' Assessments', $record->message);
                //LowerCase replace - Plural
                $record->message = str_replace(' quizzes ', ' assessments ', $record->message);
                $record->message = str_replace('quizzes ', 'assessments ', $record->message);
                $record->message = str_replace(' quizzes', ' assessments', $record->message);
                //UpperCase replace - Singular
                $record->message = str_replace(' Quiz ', ' Assessment ', $record->message);
                $record->message = str_replace('Quiz ', 'Assessment ', $record->message);
                $record->message = str_replace(' Quiz', ' Assessment', $record->message);
                //LowerCase replace - Singular
                $record->message = str_replace(' quiz ', ' assessment ', $record->message);
                $record->message = str_replace('quiz ', 'assessment ', $record->message);
                $record->message = str_replace(' quiz', ' assessment', $record->message);

                $record->save();
            }
        }
    }
}