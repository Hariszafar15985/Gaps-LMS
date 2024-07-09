<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\Support;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $nextBadge = $user->getBadges(true, true);

        $data = [
            'pageTitle' => trans('panel.dashboard'),
            'nextBadge' => $nextBadge
        ];

        if (!$user->isUser()) {
            if ($user->isOrganizationPersonnel()) {
                $organizationId = ($user->isOrganization()) ? $user->id : $user->organ_id;
                // $instructors = User::where(['role_name' => Role::$teacher, 'status' => User::$active]);
                $instructors = User::where(['role_name' => Role::$teacher, 'status' => User::$active]);
                $instructors = $instructors->pluck('id')->toArray();
                $meetingIds = Meeting::whereIn('creator_id', $instructors)->pluck('id');

                $liveRequests = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                    ->whereHas('sale')
                    ->join('users', 'reserve_meetings.user_id', 'users.id')
                    ->where('users.organ_id', $organizationId);
                //get students of own site
                $organizationStudents = $user->getOrganizationSiteStudents()->pluck('users.id')->toArray();
                if (!empty($liveRequests) && !empty($organizationStudents)) {
                    $liveRequests = $liveRequests->whereIn('users.id', $organizationStudents);
                }
            } else {
                $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id')->toArray();
                $liveRequests = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                    ->where('status', ReserveMeeting::$pending);
                    //->get();
            }
            $liveRequests = $liveRequests->get();

            $userWebinarsIds = $user->webinars->pluck('id')->toArray();
            $supports = Support::whereIn('webinar_id', $userWebinarsIds)->where('status', 'open')->get();

            $comments = Comment::whereIn('webinar_id', $userWebinarsIds)
                ->where('status', 'active')
                ->whereNull('viewed_at')
                ->get();

            $time = time();
            $firstDayMonth = strtotime(date('Y-m-01', $time));// First day of the month.
            $lastDayMonth = strtotime(date('Y-m-t', $time));// Last day of the month.

            $monthlySales = Sale::where('seller_id', $user->id)
                ->whereNull('refund_at')
                ->whereBetween('created_at', [$firstDayMonth, $lastDayMonth])
                ->get();

            $data['pendingAppointments'] = count($liveRequests);
            $data['supportsCount'] = count($supports);
            $data['commentsCount'] = count($comments);
            $data['monthlySalesCount'] = count($monthlySales) ? $monthlySales->sum('amount') : 0;
            $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);
            if ( $user->isOrganizationPersonnel()) {
                $studentsBehindProgressCount = 0;
                $studentsBehindProgress = $user->getOrganizationStudentsBehindProgress(); //collection received
                $studentsBehindProgressCount = count($studentsBehindProgress);
                $data['studentsBehindProgressCount'] = $studentsBehindProgressCount;
            } elseif ($user->role_name === Role::$teacher) {
                $pendingAssessmentsCount = 0;
                $pendingAssessments = $user->getAccessiblePendingAssessments()->get(); //query builder instance received, converted to collection
                $pendingAssessmentsCount = count($pendingAssessments);
                $data['pendingAssessmentsCount'] = $pendingAssessmentsCount;
            }
        } else {
            $webinarsIds = $user->getPurchasedCoursesIds();

            $webinars = Webinar::whereIn('id', $webinarsIds)
                ->where('status', 'active')
                ->get();

            $reserveMeetings = ReserveMeeting::where('user_id', $user->id)
                ->where('status', ReserveMeeting::$open)
                ->get();

            $supports = Support::where('user_id', $user->id)
                ->whereNotNull('webinar_id')
                ->where('status', 'open')
                ->get();

            $comments = Comment::where('user_id', $user->id)
                ->whereNotNull('webinar_id')
                ->where('status', 'active')
                ->get();

            $data['webinarsCount'] = count($webinars);
            $data['supportsCount'] = count($supports);
            $data['commentsCount'] = count($comments);
            $data['reserveMeetingsCount'] = count($reserveMeetings);
            $data['monthlyChart'] = $this->getMonthlySalesOrPurchase($user);

            $data['teachers'] = User::where('role_name', Role::$teacher)->get();
        }


        return view(getTemplate() . '.panel.dashboard', $data);
    }

    private function getMonthlySalesOrPurchase($user)
    {
        $months = [];
        $data = [];

        // all 12 months
        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create(date('Y'), $month);

            $start_date = $date->timestamp;
            $end_date = $date->copy()->endOfMonth()->timestamp;

            $months[] = trans('panel.month_' . $month);

            if (!$user->isUser()) {
                $monthlySales = Sale::where('seller_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('total_amount');

                $data[] = round($monthlySales, 2);
            } else {
                $monthlyPurchase = Sale::where('buyer_id', $user->id)
                    ->whereNull('refund_at')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                $data[] = $monthlyPurchase;
            }
        }

        return [
            'months' => $months,
            'data' => $data
        ];
    }
}
