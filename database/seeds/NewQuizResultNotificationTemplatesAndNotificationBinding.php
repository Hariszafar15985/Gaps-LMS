<?php

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;

class NewQuizResultNotificationTemplatesAndNotificationBinding extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Records for new Quiz result notifications
        //Passed Quiz template
        $quizPassed = new NotificationTemplate();
        $quizPassed->title = "Quiz passed";
        $quizPassed->template = "<p>Congratulations! Your [q.title] quiz for the class [c.title] has been marked as satisfactory.</p>";
        $quizPassed->save();
        //Failed Quiz template
        $quizFailed = new NotificationTemplate();
        $quizFailed->title = "Quiz failed";
        $quizFailed->template = "<p><a href='[link]' target='_blank'>Your [q.title] quiz for the class [c.title] has been marked as unsatisfactory and requires some rectifications. Click here to retake.</a></p>";
        $quizFailed->save();

        //If the templates were successfully created, then proceed with binding the templates
        if (
            (isset($quizPassed->id) && (int)$quizPassed->id > 0)
            && (isset($quizPassed->id) && (int)$quizPassed->id > 0)
        ) {
            $notificationSetting = Setting::where(['name' => 'notifications', 'locale' => 'en'])->first();
            if (isset($notificationSetting) && (int)$notificationSetting->id > 0) {
                $notificationSettingTranslation = SettingTranslation::where('setting_id', $notificationSetting->id);
                if (isset($notificationSettingTranslation->value) && strlen($notificationSettingTranslation->value) > 0) {
                    $bindingRequired = false;
                    //passed binding
                    if (strpos($notificationSettingTranslation->value, '"quiz_result_passed"') === false) {
                        $notificationSettingTranslation->value = str_replace("}", ',"quiz_result_passed":"'.$quizPassed->id.'"}', $notificationSettingTranslation->value);
                        $bindingRequired = true;
                    }
                    //failed binding
                    if (strpos($notificationSettingTranslation->value, '"quiz_result_failed"') === false) {
                        $notificationSettingTranslation->value = str_replace("}", ',"quiz_result_failed":"'.$quizFailed->id.'"}', $notificationSettingTranslation->value);
                        $bindingRequired = true;
                    }
                    
                    if ($bindingRequired) {
                        $notificationSettingTranslation->save();
                    }
                }
            }
        }
    }
}
