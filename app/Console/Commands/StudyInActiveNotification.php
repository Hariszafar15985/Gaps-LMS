<?php

namespace App\Console\Commands;

use App\Mail\SendNotifications;
use App\Models\Notification;
use App\Models\Role;
use App\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudyInActiveNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StudyInactiveNotification
    {type : This can be daily, weekly, fortnightly or monthly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to send the study inactivity notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $notificationType = $this->argument('type');
        // echo $notificationType; exit;
        $students = User::query()
            ->where('role_name', Role::$user) 
            ->join("course_learning", "course_learning.user_id", "users.id")
            ->join("text_lessons", "text_lessons.id", "course_learning.text_lesson_id")
            ->join("webinar_translations", "webinar_translations.webinar_id", "text_lessons.webinar_id")
            ->orderby("course_learning.id", "desc")
            ->select("users.*", "course_learning.created_at as courseStartDate", "webinar_translations.title as courseTitle")
            ->get();

// echo "<pre>"; print_r($students); exit;
        $template = "study_inactive_notification";
$data = array();

        foreach ($students as $student) {
            // echo date("Y-m-d", $student->created_at) . " - " . $student->full_name . " -ID-" . $student->id ;
            if(!in_array($student->email, $data)){
                array_push($data, $student->email);
            
            $currentDate = Carbon::today()->format("Y-m-d");
            
            $courseAttemptDate = date("Y-m-d", $student->courseStartDate); // when last time user study the course
            

            $to = Carbon::createFromFormat('Y-m-d', $currentDate);
            $from = Carbon::createFromFormat('Y-m-d', $courseAttemptDate);

                // $timestamp = $student->created_at;    
                // $datetimeFormat = 'Y-m-d';
                
                // $date = new \DateTime();
                // If you must have use time zones
                // $date = new \DateTime('now', new \DateTimeZone('Europe/Helsinki'));
                // $date->setTimestamp($timestamp);
                // echo $date->format($datetimeFormat);
                // exit;
                
                // $date1 = $currentDate;
                // $date2 = "2009-06-26";
                
                // $diff = abs(strtotime($date2) - strtotime($date1));
                // echo strtotime($currentDate); exit;

            $diffInDays = $to->diffInDays($from); // it returns difference in
            // echo "Current date " . $currentDate . " Course Attempt: ". date("Y-m-d", $student->created_at) . " Diff: " . $diffInDays; exit;
            
            // if($diffInDays > 30){
                // echo  date("Y-m-d", $student->created_at) . " - " . $student->full_name . " -ID-" . $student->id . " -email- ". $student->email . "<br>" ;
            // }
            


            $details = [
                "name" => $student->full_name,
                "title" => $student->courseTitle,
            ];
            
            switch ($notificationType) {
                case "weekly":
                    if ($diffInDays > 7) {
                        echo $student->email. " ";
                        // if($student->email == "muhammad.sajid@provelopers.net"){
                        // sendNotification($template, $notifyOptions, $student->id);
                        \Mail::to($student->email)->send(new \App\Mail\StudyInactiveMail($details));

                        // echo $student->id;
                        // }
                    }
                    break;

                case "monthly":
                    if ($diffInDays > 30) {
                        // if($student->email == "muhammad.sajid@provelopers.net"){
                        // sendNotification($template, $notifyOptions, $student->id);
                        \Mail::to($student->email)->send(new \App\Mail\StudyInactiveMail($details));
                        // echo "email " . $student->email . "<br>";

                        // }
                    }
                    break;

                default:
                    // sendNotification($template, $notifyOptions, $student->id);
                    break;
            }
            }
        }
        // exit;
        return 0;
    }
}
