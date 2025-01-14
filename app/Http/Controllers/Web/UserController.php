<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\QuizController;
use App\Http\Requests\UploadUserDocumentValidation;
use App\Models\AuditTrail;
use App\Models\Badge;
use App\Models\BecomeInstructor;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\ReserveMeeting;
use App\Models\Sale;
use App\Models\UserDocument;
use App\Models\UserOccupation;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use App\Models\Follow;
use App\Models\Meeting;
use App\Models\ProfileNotes;
use App\Models\QuizzesResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use stdClass;

class UserController extends Controller
{

    public function dashboard()
    {
        return view(getTemplate() . '.panel.dashboard');
    }

    public function profile(Request $request, $id)
    {
        $authUser = auth()->user();
        $teacherProfile = User::find($id);
        $isTeacherProfileRequest = $teacherProfile->isTeacher();

        if (!in_array($authUser->role_name, [Role::$admin, Role::$teacher, Role::$user])) {
            abort(403);
        }

        if ($authUser->isUser() && !$isTeacherProfileRequest) {
            abort(403);
        }
        $user = User::where('id', $id)
            ->whereIn('role_name', [Role::$organization, Role::$teacher, Role::$user])
            ->with(['auditTrail', 'attendance', 'profileNotes', 'userInfo', 'manager', 'occupiedBreaks' => function ($query) {
                    $query->with('requestedBy');
                },
                'placementNotes'
            ])
            ->first();
        $attendance = array();
        if (!empty($user->attendance) && $user->attendance->count()) {
            //get first date
            $attendance['beginDate'] = $user->attendance->sortBy('check_in_time', 0)->first();

            //TODO - Note: Please do not remove the comments until the attendance feature and pattern is finalized
            //$startWeekNum = date("W", strtotime($attendance['beginDate']->check_in_time));
            //$startYear = date("Y", strtotime($attendance['beginDate']->check_in_time));
            // $rangeFirst = new \DateTime();
            // $rangeFirst->setISODate($startYear, $startWeekNum);
            // $range['week_start'] = $rangeFirst->format('Y-m-d');
            // $rangeFirst->modify('+6 days');
            // $range['week_end'] = $rangeFirst->format('Y-m-d');

            //get last date
            $attendance['endDate'] = $user->attendance->sortBy('check_in_time', 1)->first();

            //$lastWeekNum = date("W", strtotime($attendance['endDate']->check_in_time));
            //$lastYear = date("Y", strtotime($attendance['endDate']->check_in_time));
            //build week spans in the form of array
            //week = array();
        }
        $auditTrails = null;
        if (!empty($user->auditTrail) && $user->auditTrail->count()) {
            $auditTrails = $user->auditTrail->sortBy('created_at', 1);
        }

        $userBadges = $user->getBadges();

        if (!$user) {
            abort(404);
        }

        if (isset($user->manager_id)) {
        }

        $meeting = Meeting::where('creator_id', $user->id)
            ->with([
                'meetingTimes'
            ])
            ->first();

        $times = [];
        if (!empty($meeting->meetingTimes)) {
            $times = convertDayToNumber($meeting->meetingTimes->groupby('day_label')->toArray());
        }

        $followings = $user->following();
        $followers = $user->followers();

        $authUserIsFollower = false;
        if (auth()->check()) {
            $authUserIsFollower = $followers->where('follower', auth()->id())
                ->where('status', Follow::$accepted)
                ->first();
        }

        $userMetas = $user->userMetas;
        $occupations = $user->occupations()
            ->with([
                'category'
            ])->get();

        //Webinars/Courses
        if (!$user->isUser()) { //not student
            $webinars = Webinar::where('status', Webinar::$active)
                ->where('private', false)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                })
                ->orderBy('updated_at', 'desc')
                ->with(['teacher' => function ($qu) {
                    $qu->select('id', 'full_name', 'avatar');
                }, 'reviews', 'tickets', 'feature'])
                ->get();
        } else { //for student profile
            $purchaseQuery = Sale::where('buyer_id', $user->id)
                ->whereNull('refund_at');

            $webinarSales = deepClone($purchaseQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id')->get();
            $webinars = collect();
            $webinarSlugs = array();
            foreach ($webinarSales as $webinarSale) {
                if (isset($webinarSale->webinar)) {
                    $webinarSale->webinar->registerDate = $webinarSale->created_at;
                    $webinars->push($webinarSale->webinar);
                    $webinarSlugs[] = $webinarSale->webinar->slug;
                }
            }
            //Fetch course content details according to student
            $coursesDetails = collect();
            foreach ($webinarSlugs as $webinarSlug) {
                $content = (new \App\Http\Controllers\Web\WebinarController)->course($webinarSlug, true, $id);
                $contentDetail = new stdClass();
                $contentDetail->content = $content;
                $contentDetail->slug = $webinarSlug;
                $coursesDetails->push($contentDetail);
            }
        }
        //Meetings/Appointments
        if (!$user->isUser()) { //not student
            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $appointments = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->whereNotNull('reserved_at')
                ->where('status', '!=', ReserveMeeting::$canceled)
                ->count();
        } else { //student profile
            $appointmentSales = deepClone($purchaseQuery)->whereNotNull('meeting_id')
                ->whereNull('webinar_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id')->get();
            $appointments = collect();
            foreach ($appointmentSales as $appointmentSale) {
                if (isset($appointmentSales->meeting)) {
                    $appointments->push($appointmentSale->meeting);
                }
            }
        }

        $studentsIds = Sale::whereNull('refund_at')
            ->where('seller_id', $user->id)
            ->whereNotNull('webinar_id')
            ->pluck('buyer_id')
            ->toArray();
        $user->students_count = count(array_unique($studentsIds));

        $request->merge([
            'userId' => $id,
            'fetchQuizDataOnly' => true,
        ]);

        $pendingQuizzesCount = 0;
        if ($user->isUser()) {
            $pendingQuizzesCount = QuizzesResult::where([
                'user_id' => $user->id,
                'status' => QuizzesResult::$waiting,
            ])->count();
        }

        $quizResultsListing = (new QuizController)->myResults($request);

        $lastLogin = $user->getLastLoginAudit();
        if (empty($lastLogin)) {
            $lastLogin = null;
        }

        //set requestedUser in session
        $request->session()->put('requestedUser',$user);
        // dd(session()->all());

        $data = [
            'pageTitle' => $user->full_name . ' ' . trans('public.profile'),
            'user' => $user,
            'userBadges' => $userBadges,
            'attendance' => $attendance,
            'auditTrails' => $auditTrails,
            'lastLogin' => $lastLogin,
            'meeting' => $meeting,
            'times' => $times,
            'userRates' => $user->rates(),
            'userFollowers' => $followers,
            'userFollowing' => $followings,
            'authUserIsFollower' => $authUserIsFollower,
            'educations' => $userMetas->where('name', 'education'),
            'experiences' => $userMetas->where('name', 'experience'),
            'occupations' => $occupations,
            'webinars' => $webinars,
            'appointments' => $appointments,
            'quizResultsListing' => $quizResultsListing,
            'pendingQuizzesCount' => $pendingQuizzesCount,
            'coursesDetails' => isset($coursesDetails) ? $coursesDetails : null,
            'allUserDocumentTypes' => UserDocument::getDocumentTypeEnumValues()
        ];
        if ($user->isUser()) { //student profile
            $data['pendingPayments'] = $user->getPendingPayments();
            return view('web.default.user.student_profile', $data);
        } else { //not student
            return view('web.default.user.profile', $data);
        }
    }

    public function uploadUserDocument(UploadUserDocumentValidation $request)
    {
        $filename = uploadFile($request, 'document', 'store/' . $request->user_id . '/user_documents');
        $documentData = [
            'user_id' => $request->user_id,
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'document' => $filename,
            'uploaded_by' => Auth::user()->id,
            'created_at' => date("Y-m-d H:i:s", time()),
            'updated_at' => date("Y-m-d H:i:s", time())
        ];
        $res = UserDocument::insert($documentData);
        return $res;
    }

    public function deleteUserDocument(Request $request)
    {
        $response = array('res' => 0);
        $id = $request->did;

        $uDocument = UserDocument::where('id', $id)->first();
        if ($uDocument) {
            $filePath = public_path('/store/' . $uDocument->user_id . '/user_documents/' . $uDocument->document);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $uDocument->delete();
            $response = array('res' => 1);
        }
        return json_encode($response);
    }

    function downloadEnrollmentPdf(Request $request)
    {
        $id = $request->id;
        $user = User::where('id', $id)
            ->whereIn('role_name', [Role::$organization, Role::$teacher, Role::$user])
            ->with('userInfo')
            ->first();
        $data = ['user' => $user];
        $html = view('web.default.user.profile_tabs.enrollment_pdf', $data);
        $pdf = Pdf::loadHTML($html);
        return $pdf->download($id . '_user_enrollment.pdf');
    }

    public function followToggle($id)
    {
        $authUser = auth()->user();
        $user = User::where('id', $id)->first();

        $followStatus = false;
        $follow = Follow::where('follower', $authUser->id)
            ->where('user_id', $user->id)
            ->first();

        if (empty($follow)) {
            Follow::create([
                'follower' => $authUser->id,
                'user_id' => $user->id,
                'status' => Follow::$accepted,
            ]);

            $followStatus = true;
        } else {
            $follow->delete();
        }

        return response()->json([
            'code' => 200,
            'follow' => $followStatus
        ], 200);
    }

    public function availableTimes(Request $request, $id)
    {
        $timestamp = $request->get('timestamp');

        $user = User::where('id', $id)
            ->whereIn('role_name', [Role::$teacher, Role::$organization])
            ->where('status', 'active')
            ->first();

        if (!$user) {
            abort(404);
        }

        $meeting = Meeting::where('creator_id', $user->id)
            ->with(['meetingTimes'])
            ->first();

        $meetingTimes = [];

        if (!empty($meeting->meetingTimes)) {
            foreach ($meeting->meetingTimes->groupBy('day_label') as $day => $meetingTime) {

                foreach ($meetingTime as $time) {
                    $can_reserve = true;

                    $explodetime = explode('-', $time->time);
                    $secondTime = dateTimeFormat(strtotime($explodetime['0']), 'H') * 3600 + dateTimeFormat(strtotime($explodetime['0']), 'i') * 60;

                    $reserveMeeting = ReserveMeeting::where('meeting_time_id', $time->id)
                        ->where('day', dateTimeFormat($timestamp, 'Y-m-d'))
                        ->where('meeting_time_id', $time->id)
                        ->first();

                    if ($reserveMeeting && ($reserveMeeting->locked_at || $reserveMeeting->reserved_at)) {
                        $can_reserve = false;
                    }

                    if ($timestamp + $secondTime < time()) {

                        $can_reserve = false;
                    }
                    $meetingTimes[$day]["times"][] = ["id" => $time->id, "time" => $time->time, "can_reserve" => $can_reserve];
                }
            }
        }

        return response()->json($meetingTimes[strtolower(dateTimeFormat($timestamp, 'l'))], 200);
    }

    public function instructors(Request $request)
    {
        $seoSettings = getSeoMetas('instructors');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.instructors');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.instructors');
        $pageRobot = getPageRobot('instructors');

        $data = $this->handleInstructorsOrOrganizationsPage($request, Role::$teacher);

        $data['title'] = trans('home.instructors');
        $data['page'] = 'instructors';
        $data['pageTitle'] = $pageTitle;
        $data['pageDescription'] = $pageDescription;
        $data['pageRobot'] = $pageRobot;

        return view('web.default.pages.instructors', $data);
    }

    public function organizations(Request $request)
    {
        $seoSettings = getSeoMetas('organizations');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.organizations');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.organizations');
        $pageRobot = getPageRobot('organizations');

        $data = $this->handleInstructorsOrOrganizationsPage($request, Role::$organization);

        $data['title'] = trans('home.organizations');
        $data['page'] = 'organizations';
        $data['pageTitle'] = $pageTitle;
        $data['pageDescription'] = $pageDescription;
        $data['pageRobot'] = $pageRobot;

        return view('web.default.pages.instructors', $data);
    }

    public function handleInstructorsOrOrganizationsPage(Request $request, $role)
    {
        $query = User::where('role_name', $role)
            //->where('verified', true)
            ->where('users.status', 'active')
            ->where(function ($query) {
                $query->where('users.ban', false)
                    ->orWhere(function ($query) {
                        $query->whereNotNull('users.ban_end_at')
                            ->orWhere('users.ban_end_at', '<', time());
                    });
            })
            ->with(['meeting' => function ($query) {
                $query->with('meetingTimes');
                $query->withCount('meetingTimes');
            }]);

        $instructors = $this->filterInstructors($request, deepClone($query), $role)
            ->paginate(6);

        if ($request->ajax()) {
            $html = null;

            foreach ($instructors as $instructor) {
                $html .= '<div class="col-12 col-lg-4">';
                $html .= (string)view()->make('web.default.pages.instructor_card', ['instructor' => $instructor]);
                $html .= '</div>';
            }

            return response()->json([
                'html' => $html,
                'last_page' => $instructors->lastPage(),
            ], 200);
        }

        if (empty($request->get('sort')) or !in_array($request->get('sort'), ['top_rate', 'top_sale'])) {
            $bestRateInstructorsQuery = $this->getBestRateUsers(deepClone($query), $role);

            $bestSalesInstructorsQuery = $this->getTopSalesUsers(deepClone($query), $role);

            $bestRateInstructors = $bestRateInstructorsQuery
                ->limit(8)
                ->get();

            $bestSalesInstructors = $bestSalesInstructorsQuery
                ->limit(8)
                ->get();
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $data = [
            'pageTitle' => trans('home.instructors'),
            'instructors' => $instructors,
            'instructorsCount' => deepClone($query)->count(),
            'bestRateInstructors' => $bestRateInstructors ?? null,
            'bestSalesInstructors' => $bestSalesInstructors ?? null,
            'categories' => $categories,
        ];

        return $data;
    }

    private function filterInstructors($request, $query, $role)
    {
        $categories = $request->get('categories', null);
        $sort = $request->get('sort', null);
        $availableForMeetings = $request->get('available_for_meetings', null);
        $hasFreeMeetings = $request->get('free_meetings', null);
        $withDiscount = $request->get('discount', null);
        $search = $request->get('search', null);


        if (!empty($categories) and is_array($categories)) {
            $userIds = UserOccupation::whereIn('category_id', $categories)->pluck('user_id')->toArray();

            $query->whereIn('users.id', $userIds);
        }

        if (!empty($sort) and $sort == 'top_rate') {
            $query = $this->getBestRateUsers($query, $role);
        }

        if (!empty($sort) and $sort == 'top_sale') {
            $query = $this->getTopSalesUsers($query, $role);
        }

        if (!empty($availableForMeetings) and $availableForMeetings == 'on') {
            $hasMeetings = DB::table('meetings')
                ->where('meetings.disabled', 0)
                ->join('meeting_times', 'meetings.id', '=', 'meeting_times.meeting_id')
                ->select('meetings.creator_id', DB::raw('count(meeting_id) as counts'))
                ->groupBy('creator_id')
                ->orderBy('counts', 'desc')
                ->get();

            $hasMeetingsInstructorsIds = [];
            if (!empty($hasMeetings)) {
                $hasMeetingsInstructorsIds = $hasMeetings->pluck('creator_id')->toArray();
            }

            $query->whereIn('users.id', $hasMeetingsInstructorsIds);
        }

        if (!empty($hasFreeMeetings) and $hasFreeMeetings == 'on') {
            $freeMeetingsIds = Meeting::where('disabled', 0)
                ->where(function ($query) {
                    $query->whereNull('amount')->orWhere('amount', '0');
                })->groupBy('creator_id')
                ->pluck('creator_id')
                ->toArray();

            $query->whereIn('users.id', $freeMeetingsIds);
        }

        if (!empty($withDiscount) and $withDiscount == 'on') {
            $withDiscountMeetingsIds = Meeting::where('disabled', 0)
                ->whereNotNull('discount')
                ->groupBy('creator_id')
                ->pluck('creator_id')
                ->toArray();

            $query->whereIn('users.id', $withDiscountMeetingsIds);
        }

        if (!empty($search)) {
            $query->where(function ($qu) use ($search) {
                $qu->where('users.full_name', 'like', "%$search%")
                    ->orWhere('users.email', 'like', "%$search%")
                    ->orWhere('users.mobile', 'like', "%$search%");
            });
        }

        return $query;
    }

    private function getBestRateUsers($query, $role)
    {
        $query->leftJoin('webinars', function ($join) use ($role) {
            if ($role == Role::$organization) {
                $join->on('users.id', '=', 'webinars.creator_id');
            } else {
                $join->on('users.id', '=', 'webinars.teacher_id');
            }

            $join->where('webinars.status', 'active');
        })->leftJoin('webinar_reviews', function ($join) {
            $join->on('webinars.id', '=', 'webinar_reviews.webinar_id');
            $join->where('webinar_reviews.status', 'active');
        })
            ->whereNotNull('rates')
            ->select('users.*', DB::raw('avg(rates) as rates'))
            ->orderBy('rates', 'desc');

        if ($role == Role::$organization) {
            $query->groupBy('webinars.creator_id');
        } else {
            $query->groupBy('webinars.teacher_id');
        }

        return $query;
    }

    private function getTopSalesUsers($query, $role)
    {
        $query->leftJoin('sales', function ($join) {
            $join->on('users.id', '=', 'sales.seller_id')
                ->whereNull('refund_at');
        })
            ->whereNotNull('sales.seller_id')
            ->select('users.*', 'sales.seller_id', DB::raw('count(sales.seller_id) as counts'))
            ->groupBy('sales.seller_id')
            ->orderBy('counts', 'desc');

        return $query;
    }

    public function becomeInstructors()
    {
        $user = auth()->user();

        if ($user->isUser()) {
            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $occupations = $user->occupations->pluck('category_id')->toArray();

            $lastRequest = BecomeInstructor::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            $data = [
                'pageTitle' => trans('site.become_instructor'),
                'user' => $user,
                'lastRequest' => $lastRequest,
                'categories' => $categories,
                'occupations' => $occupations
            ];

            return view('web.default.user.become_instructor', $data);
        }

        abort(404);
    }

    public function becomeInstructorsStore(Request $request)
    {
        $user = auth()->user();

        if ($user->isUser()) {
            $lastRequest = BecomeInstructor::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'accept'])
                ->first();

            if (empty($lastRequest)) {
                $this->validate($request, [
                    'occupations' => 'required',
                    'certificate' => 'nullable|string',
                    'account_type' => 'required',
                    'iban' => 'required',
                    'account_id' => 'required',
                    'identity_scan' => 'required',
                    'description' => 'nullable|string',
                ]);

                $data = $request->all();

                BecomeInstructor::create([
                    'user_id' => $user->id,
                    'certificate' => $data['certificate'],
                    'description' => $data['description'],
                    'created_at' => time()
                ]);

                $user->update([
                    'account_type' => $data['account_type'],
                    'iban' => $data['iban'],
                    'account_id' => $data['account_id'],
                    'identity_scan' => $data['identity_scan'],
                    'certificate' => $data['certificate'],
                ]);

                if (!empty($data['occupations'])) {
                    UserOccupation::where('user_id', $user->id)->delete();

                    foreach ($data['occupations'] as $category_id) {
                        UserOccupation::create([
                            'user_id' => $user->id,
                            'category_id' => $category_id
                        ]);
                    }
                }
            }


            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('site.become_instructor_success_request'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function makeNewsletter(Request $request)
    {
        $this->validate($request, [
            'newsletter_email' => 'required|string|email|max:255|unique:newsletters,email'
        ]);

        $data = $request->all();
        $user_id = null;
        $email = $data['newsletter_email'];

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->email == $email) {
                $user_id = $user->id;

                $user->update([
                    'newsletter' => true,
                ]);
            }
        }

        Newsletter::create([
            'user_id' => $user_id,
            'email' => $data['newsletter_email'],
            'created_at' => time()
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('site.create_newsletter_success'),
            'status' => 'success'
        ];
        return back()->with(['toast' => $toastData]);
    }

    public function sendMessage(Request $request, $id)
    {
        if (!empty($id)) {
            $user = User::select('id', 'email')
                ->where('id', $id)
                ->first();

            if (!empty($user) and !empty($user->email)) {
                $data = $request->all();

                $validator = Validator::make($data, [
                    'title' => 'required|string',
                    'email' => 'required|email',
                    'description' => 'required|string',
                    'captcha' => 'required|captcha',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'code' => 422,
                        'errors' => $validator->errors()
                    ], 422);
                }

                $mail = [
                    'title' => $data['title'],
                    'message' => trans('site.you_have_message_from', ['email' => $data['email']]) . "\n" . $data['description'],
                ];

                try {
                    \Mail::to($user->email)->send(new \App\Mail\SendNotifications($mail));

                    return response()->json([
                        'code' => 200
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        'code' => 500,
                        'message' => trans('site.server_error_try_again')
                    ]);
                }
            }

            return response()->json([
                'code' => 403,
                'message' => trans('site.user_disabled_public_message')
            ]);
        }
    }

    public function addUpdateProfileNote(Request $request)
    {
        $user = auth()->user();
        if ( $user->isAdmin() || $user->isTeacher()) {
            $this->validate($request, [
                'user_id'   =>  'required|integer|min:1',
                'title'     =>  'required|min:1',
                'message'   =>  'required|min:1',
            ]);

            $userId = $request->get('user_id');
            //common data
            $notesData = [
                'user_id'       =>  $userId,
                'title'         =>  $request->get('title'),
                'message'       =>  $request->get('message'),
                'creator_id'    =>  $user->id,
            ];

            $noteId = $request->get('noteId');
            $route = route('get.user.profile', ['id' => $userId]) . '?tab=notes';
            //Audit Noted edit
            $ip = null;
            $ip = getClientIp();
            $auditedUser = User::find($userId);

            if (isset($noteId) && (int)$noteId > 0) { //edit
                unset($notesData['creator_id']);
                $profile = ProfileNotes::find((int)$noteId);
                $profile->update($notesData);

                //audit edited note
                $auditMessage = "Note edited on user profile.<br /><span class='font-italic'>({$notesData['message']})</span>";
                $audit = new AuditTrail();
                $audit->user_id = $userId;
                $audit->organ_id = $auditedUser->organ_id;
                $audit->role_name = $auditedUser->role_name;
                $audit->audit_type = AuditTrail::auditType['profile_note_edited'];
                $audit->added_by = $user->id;
                $audit->description = $auditMessage;
                $audit->ip = ip2long($ip);
                $audit->save();
                return redirect($route);
            } elseif (ProfileNotes::Create($notesData)) { //create
                //audit added note
                $auditMessage = "Note added on user profile.<br /><span class='font-italic'>(" . $notesData['message'] . ")</span>";
                $audit = new AuditTrail();
                $audit->user_id = $userId;
                $audit->organ_id = $auditedUser->organ_id;
                $audit->role_name = $auditedUser->role_name;
                $audit->audit_type = AuditTrail::auditType['profile_note_added'];
                $audit->added_by = $user->id;
                $audit->description = $auditMessage;
                $audit->ip = ip2long($ip);
                $audit->save();
                return redirect($route);
            } else {
                $toastData = [
                    'title' => trans('public.note_add_failed'),
                    'msg' => trans('public.something_went_wrong_note_add'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        } else {
            abort(403);
        }
    }

    public function removeProfileNote($id)
    {
        $user = auth()->user();
        if (!$user->isUser() && !$user->isTeacher()) {
            if ($id > 0) {
                $profileNote = ProfileNotes::find($id);
                $userId = $profileNote->user_id; //profile of user on which redirection is to be made again
                if ($profileNote->delete()) {
                    $route = route('get.user.profile', ['id' => $userId]) . '?tab=notes';
                    return redirect($route);
                } else {
                    abort(404);
                }
            }
        } else {
            abort(404);
        }
    }

    /**
     * studentDocVisiblity function is created to show or hide the student documents visibility
     *
     * @param Request $request
     * @return boolean true or abort to 404
     */
    public function studentDocVisiblity(Request $request) {
        $documentId = $request->did;
        $document = UserDocument::find($documentId);
        if($document) {
            ($document->student_visibility == 1) ? $document->update(["student_visibility" => 0]) : $document->update(["student_visibility" => 1]);
            return true;
        }
        abort(404);
    }
}
