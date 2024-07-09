<?php

namespace App\Console\Commands;

use App\Mail\SendNotifications;
use App\Models\Notification;
use App\Models\Role;
use App\Models\OrganizationSite;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudentInactiveNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'StudentInactiveNotification
                            {type : This can be daily, weekly, fortnightly or monthly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to send the students inactivity notifications';

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
        $organizations = User::query()
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->with('organizationSites')
            ->get();

        $notificationType = $this->argument('type');

        foreach ($organizations as $organization) {

            $emailSettings = $organization->inActivityEmailSettings()->where('active',1)->first();

            if ( ! empty( $emailSettings )
                && in_array( $notificationType, json_decode($emailSettings->type,true) ) )
            {
                $organizationManagers = $organization->getOrganizationManagers()->get();
                foreach ($organizationManagers as $organizationManager) {
                    if ( ! empty( $organizationManager->email ) ) {
                        $managerStudents = $organizationManager->getManagerStudents()->get();
                        $inActivityList  = $this->generateInactiveList( $managerStudents, $notificationType );

                        foreach ($managerStudents as $managerStudent) {
                            $attendance = $managerStudent->attendance()
                                ->whereDate('check_in_time', Carbon::today())
                                ->first();

                            if ( empty( $attendance ) ) {
                                $sites = $managerStudent->organizationSites;
                                if ( ! empty( $sites ) ) {
                                    foreach($sites as $site) {
                                        if ( isset( $inActivityList[$site->name] ) ) {
                                            $inActivityList[$site->name][] = $managerStudent;
                                        } else {
                                            $inActivityList[$site->name] = [$managerStudent];
                                        }
                                    }
                                }
                            }
                        }

                        if ( ! empty( $inActivityList ) ) {
                            $title   = ucwords($notificationType) . ' students inactivity';
                            $message = ucwords($notificationType) . ' students inactivity email sent.';
                            $this->sendNotification( $organizationManager, $inActivityList, $title, $message );
                        }
                    }
                }
            }
        }

        return 0;
    }


    private function generateInactiveList( $managerStudents, $notificationType )
    {
        $inActivityList = [];
        foreach ( $managerStudents as $managerStudent ) {
            $attendance = $managerStudent->attendance();

            switch ( $notificationType ) {
                case 'weekly':
                    $days = 7;
                    $date = Carbon::now()->subDays(7)->format('Y-m-d');
                    break;
                case 'fortnightly':
                    $days = 14;
                    $date = Carbon::now()->subDays(14)->format('Y-m-d');
                    break;
                case 'monthly':
                    $days = 30;
                    $date = Carbon::now()->subDays(30)->format('Y-m-d');
                    break;
                default:
                    $days = 1;
                    $date = Carbon::today()->subDay()->format('Y-m-d');
                    break;
            }

            for ( $i = 1; $i < $days; $i++ ) {
                $wereDate = Carbon::parse($date)->addDays($i)->format('Y-m-d');
                $attended = $attendance->whereDate('check_in_time', $wereDate)->first();
                if ( empty( $attended ) ) {
                    $site = OrganizationSite::find($managerStudent->organization_site);
                    if ( ! empty( $site ) ) {
                        if ( isset( $inActivityList[$site->name] ) ) {
                            $inActivityList[$site->name]["$wereDate"][] = $managerStudent;
                        } else {
                            $inActivityList[$site->name] = [
                                "$wereDate" => [$managerStudent]
                            ];
                        }
                    }
                }
            }

        }

        return $inActivityList;
    }


    private function sendNotification($organizationManager, $inActivityList, $title, $message) {
        try {

            $html = '<h1>Inactivity of students w.r.t Organization Site</h1> <br>';
            $list = "<table cellpadding='1' cellspacing='1' width='100%' id='templatePreheader'>
                        <thead>
                        <tr>
                            <td>Name</td>
                            <td>Email</td>
                            <td>Phone</td>
                            <td>Consultant</td>
                            <td>Organization Site</td>
                            <td>Live Classes</td>
                            <td>Progress</td>
                            <td>Assessments</td>
                            <td>Certificates</td>
                            <td>Date</td>
                        </tr>
                        </thead>
                        <tbody>";

            foreach ($inActivityList as $site => $dates) {
                if($dates && !empty($dates)) {
                    foreach ($dates as $date => $students) {
                        if ($students && !empty($students)) {
                            foreach ($students as $student) {
                                if ( $student ) {
                                    $user = User::find($student->id);
                                    $printProgress = '-';
                                    if( $user->isBehindProgress() == true ) {
                                        $printProgress = "Behind Progress";
                                    }
                                    $list .= "
                                    <tr>
                                        <td> $student->full_name </td>
                                        <td> $student->email </td>
                                        <td> $student->mobile </td>
                                        <td> ". $user->manager->full_name ." </td>
                                        <td> $site </td>
                                        <td> ". count($user->getPurchasedCoursesIds()) ." </td>
                                        <td style='color: red;'> ".$printProgress."</td>
                                        <td>". count($user->getActiveQuizzesResults()) ."</td>
                                        <td> ". count($user->certificates) ." </td>
                                        <td> $date </td>
                                    </tr>
                                    ";
                                }
                            }
                        }
                    }
                }
            }
            $list .= '</tbody>
            </table>';
            $html .= $list;
            if ( getenv('APP_ENV') == 'production' ) {
                Mail::to( $organizationManager->email )
                    ->send(new SendNotifications(['title' => $title, 'message' => $html], true));
            }

            Notification::query()->create([
                'user_id'    => $organizationManager->id,
                'group_id'   => $organizationManager->group_id,
                'title'      => $title,
                'message'    => $message,
                'sender'     => Notification::$SystemSender,
                'type'       => 'organizations',
                'created_at' => time()
            ]);

            Log::channel('mail')->debug('Cron Mail sent to : ' . $organizationManager->email);

        } catch (\Exception $e) {
            Log::channel('mail')->error('Exception : ' . $e->getMessage() );
        }
    }
}
