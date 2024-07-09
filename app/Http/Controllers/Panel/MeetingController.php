<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\User;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    public function setting(Request $request)
    {
        $user = auth()->user();

        $meeting = Meeting::where('creator_id', $user->id)
            ->with([
                'meetingTimes'
            ])
            ->first();

        if (empty($meeting)) {
            $meeting = Meeting::create([
                'creator_id' => $user->id,
                'created_at' => time()
            ]);
        }

        $meetingTimes = [];
        foreach ($meeting->meetingTimes->groupBy('day_label') as $day => $meetingTime) {

            $times = 0;
            foreach ($meetingTime as $time) {

                $meetingTimes[$day]["times"][] = $time;

                $explodetime = explode('-', $time->time);
                $times += strtotime($explodetime[1]) - strtotime($explodetime[0]);
            }
            $meetingTimes[$day]["hours_available"] = round($times / 3600, 2);

        }

        $data = [
            'pageTitle' => trans('meeting.meeting_setting_page_title'),
            'meeting' => $meeting,
            'meetingTimes' => $meetingTimes,
        ];

        return view(getTemplate() . '.panel.meeting.settings', $data);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'amount' => 'required',
            'discount' => 'nullable',
            'disabled' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $meeting = Meeting::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($meeting)) {
            $meeting->update([
                'amount' => $data['amount'],
                'discount' => $data['discount'],
                'disabled' => !empty($data['disabled']) ? 1 : 0,
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function saveTime(Request $request)
    {
        $user = auth()->user();
        $meeting = Meeting::where('creator_id', $user->id)->first();
        $data = $request->all();

        if (!empty($meeting)) {
            $time = $data['time'];
            $day = $data['day'];

            $explodeTime = explode('-', $time);

            if (!empty($explodeTime[0]) and !empty($explodeTime[1])) {
                $start_time = date("H:i", strtotime($explodeTime[0]));
                $end_time = date("H:i", strtotime($explodeTime[1]));

                if (strtotime($end_time) >= strtotime($start_time)) {
                    $checkTime = MeetingTime::where('meeting_id', $meeting->id)
                        ->where('day_label', $data)
                        ->where('time', $time)
                        ->first();

                    if (empty($checkTime)) {
                        MeetingTime::create([
                            'meeting_id' => $meeting->id,
                            'day_label' => $day,
                            'time' => $time,
                            'created_at' => time(),
                        ]);

                        return response()->json([
                            'code' => 200
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'error' => 'contradiction'
                    ], 422);
                }
            }
        }

        return response()->json([], 422);
    }

    public function deleteTime(Request $request)
    {
        $user = auth()->user();
        $meeting = Meeting::where('creator_id', $user->id)->first();
        $data = $request->all();
        $timeIds = $data['time_id'];

        if (!empty($meeting) and !empty($timeIds) and is_array($timeIds)) {

            $meetingTimes = MeetingTime::whereIn('id', $timeIds)
                ->where('meeting_id', $meeting->id)
                ->get();

            if (!empty($meetingTimes)) {
                foreach ($meetingTimes as $meetingTime) {
                    $meetingTime->delete();
                }

                return response()->json([], 200);
            }
        }

        return response()->json([], 422);
    }

    public function temporaryDisableMeetings(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();

        $meeting = Meeting::where('creator_id', $user->id)
            ->first();

        if (!empty($meeting)) {
            $meeting->update([
                'disabled' => (!empty($data['disable']) and $data['disable'] == 'true') ? 1 : 0,
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    /** Method to display form for providing facility to organization to book meeting between Student and Instructor */
    public function newMeeting(Request $request)
    {
        $user = auth()->user();
        if ($user->isOrganizationPersonnel()) {
            $queryParams = $request->query();
            //Getting all instructors
            //$instructors = $user->getOrganizationTeachers;
            $instructors = User::where(['status' => User::$active, 'role_name' => Role::$teacher])->get();
            $requestedInstructor = (isset($queryParams['instructor'])) ? (int)$queryParams['instructor'] : null;
            $selectedInstructor = null;
            if (isset($requestedInstructor) && $requestedInstructor) {
                $selectedInstructor = User::where(['role_name' => Role::$teacher, 'id' => $requestedInstructor])->first(); 
            } else {
                foreach($instructors as $instructor) {
                    $selectedInstructor = $instructor;
                    break;
                }
            }
            if (isset($selectedInstructor) && $selectedInstructor->id) {
                $meeting = Meeting::where('creator_id', $selectedInstructor->id)
                ->with([
                    'meetingTimes'
                ])
                ->first();
            
                $times = [];
                if (!empty($meeting->meetingTimes)) {
                    $times = convertDayToNumber($meeting->meetingTimes->groupby('day_label')->toArray());
                }
            } else {
                $meeting = null;
                $times = null;
            }
            if ($user->isOrganization()                                                                             ) {
                $students = $user->getOrganizationStudents;
            } else {
                $students = $user->getOrganizationSiteStudents()->get();
            }
            $selectedStudentId = (isset($queryParams['student'])) ? (int)$queryParams['student'] : null;
            $selectedStudent = null;
            if (isset($selectedStudentId) && $selectedStudentId > 0) {
                $selectedStudent = User::where(['role_name' => Role::$user, 'id' => $selectedStudentId])->first();
            } else {
                foreach($students as $student) {
                    $selectedStudent = $student;
                }
            }

            $data = [
                'pageTitle' => trans('meeting.meeting_create_page_title'),
                'instructors'   => $instructors,
                'selectedInstructor'   => $selectedInstructor,
                'students'   => $students,
                'selectedStudent'   => $selectedStudent,
                'meeting'   => $meeting,
                'times'   => $times,
            ];
    
            return view(getTemplate() . '.panel.meeting.book_instructor_student_meeting', $data);

        } else {
            abort(404);
        }
    }

    /**
     * Method to list all meetings scheduled against Organization instructors
     */
    public function getOrganizationMeetings(Request $request)
    {
        $user = auth()->user();
        if ($user->isOrganizationPersonnel()) {
            $organizationId = ($user->isOrganization()) ? $user->id : $user->organ_id;
            $instructors = User::where(['role_name' => Role::$teacher, 'status' => User::$active]);
            if (!empty($organizationStudents)) {
                $instructors = $instructors->whereIn('id', $organizationStudents);
            }

            $instructors = $instructors->pluck('id')->toArray();
            $meetingIds = Meeting::whereIn('creator_id', $instructors)->pluck('id');

            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
            ->whereHas('sale')
            ->join('users', 'reserve_meetings.user_id', 'users.id')
            ->where('users.organ_id', $organizationId);
            //get students of own site
            $organizationStudents = $user->getOrganizationSiteStudents()->pluck('users.id')->toArray();
            if (!empty($reserveMeetingsQuery) && !empty($organizationStudents)) {
                $reserveMeetingsQuery = $reserveMeetingsQuery->whereIn('users.id', $organizationStudents);
            }
            $pendingReserveCount = deepClone($reserveMeetingsQuery)->where('reserve_meetings.status', \App\models\ReserveMeeting::$pending)->count();
            $totalReserveCount = deepClone($reserveMeetingsQuery)->count();
            $sumReservePaid = deepClone($reserveMeetingsQuery)->sum('paid_amount');

            $userIdsReservedTime = deepClone($reserveMeetingsQuery)->pluck('user_id')->toArray();
            $usersReservedTimes = User::select('id', 'full_name')
                ->whereIn('id', array_unique($userIdsReservedTime))
                ->get();

            $reserveMeetingsQuery = $this->filters(deepClone($reserveMeetingsQuery), $request);
            $reserveMeetingsQuery = $reserveMeetingsQuery->with(['meetingTime', 'meeting', 'user' => function ($query) {
                $query->select('id', 'full_name', 'avatar', 'email');
            }]);

            $reserveMeetings = $reserveMeetingsQuery
                ->orderBy('reserve_meetings.created_at', 'desc')
                ->paginate(10);


            $activeMeetingTimeIds = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where('reserve_meetings.status', ReserveMeeting::$pending)
                ->pluck('meeting_time_id')
                ->toArray();

            $meetingTimesCount = array_count_values($activeMeetingTimeIds);
            $activeMeetingTimes = MeetingTime::whereIn('id', $activeMeetingTimeIds)->get();

            $activeHoursCount = 0;
            foreach ($activeMeetingTimes as $time) {
                $explodetime = explode('-', $time->time);
                $hour = strtotime($explodetime[1]) - strtotime($explodetime[0]);

                if (!empty($meetingTimesCount) and is_array($meetingTimesCount) and !empty($meetingTimesCount[$time->id])) {
                    $hour = $hour * $meetingTimesCount[$time->id];
                }

                $activeHoursCount += $hour;
            }

            $data = [
                'pageTitle' => trans('meeting.meeting_requests_page_title'),
                'reserveMeetings' => $reserveMeetings,
                'pendingReserveCount' => $pendingReserveCount,
                'totalReserveCount' => $totalReserveCount,
                'sumReservePaid' => $sumReservePaid,
                'activeHoursCount' => $activeHoursCount,
                'usersReservedTimes' => $usersReservedTimes,
            ];

            return view(getTemplate() . '.panel.meeting.requests', $data);
        } else {
            abort(404);
        }
    }

    public function filters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $day = $request->get('day');
        $instructor_id = $request->get('instructor_id');
        $student_id = $request->get('student_id');
        $status = $request->get('status');
        $openMeetings = $request->get('open_meetings');

        if (!empty($from) and !empty($to)) {
            $from = strtotime($from);
            $to = strtotime($to);

            $query->whereBetween('created_at', [$from, $to]);
        } else {
            if (!empty($from)) {
                $from = strtotime($from);
                $query->where('created_at', '>=', $from);
            }

            if (!empty($to)) {
                $to = strtotime($to);

                $query->where('created_at', '<', $to);
            }
        }

        if (!empty($day) and $day != 'all') {
            $meetingTimeIds = $query->pluck('meeting_time_id');
            $meetingTimeIds = MeetingTime::whereIn('id', $meetingTimeIds)
                ->where('day_label', $day)
                ->pluck('id');

            $query->whereIn('meeting_time_id', $meetingTimeIds);
        }

        if (!empty($instructor_id) and $instructor_id != 'all') {

            $meetingsIds = Meeting::where('creator_id', $instructor_id)
                ->where('disabled', false)
                ->pluck('id')
                ->toArray();

            $query->whereIn('meeting_id', $meetingsIds);
        }

        if (!empty($student_id) and $student_id != 'all') {
            $query->where('user_id', $student_id);
        }


        if (!empty($status) and $status != 'All') {
            $query->where('status', strtolower($status));
        }

        if (!empty($openMeetings) and $openMeetings == 'on') {
            $query->where('status', 'open');
        }

        return $query;
    }
    

    /* public function getInstructorCalendar(Request $request)
    {
        $data = $request->all();
        if (!isset($data['instructor_id'])) {
            return response()->json([], 422);
        }
        $meeting = Meeting::where('creator_id', $data['instructor_id'])
            ->with([
                'meetingTimes'
            ])
            ->first();
        
        $view = 

        $times = [];
        if (!empty($meeting->meetingTimes)) {
            $times = convertDayToNumber($meeting->meetingTimes->groupby('day_label')->toArray());
        }
    } */
}
