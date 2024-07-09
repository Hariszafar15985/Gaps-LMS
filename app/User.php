<?php

namespace App;

use App\Models\CourseLearning;
use App\Models\Accounting;
use App\Models\Attendance;
use App\Models\AuditTrail;
use App\Models\Badge;
use App\Models\Meeting;
use App\Models\Noticeboard;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\QuizzesResult;
use App\Models\Role;
use App\Models\Follow;
use App\Models\OrganizationSite;
use App\Models\ProfileNotes;
use App\Models\Quiz;
use App\Models\Sale;
use App\Models\Section;
use App\Models\StudentDeclaration;
use App\Models\StudentNotificationSetting;
use App\Models\UserBreak;
use App\Models\UserDocument;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use App\PlacementNotes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    static $active = 'active';
    static $pending = 'pending';
    static $inactive = 'inactive';

    protected $dateFormat = 'U';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $hidden = [
        'password', 'remember_token', 'google_id', 'facebook_id', 'role_id'
    ];

    static $statuses = [
        'active', 'pending', 'inactive'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    private $permissions;
    private $user_group;
    //private $userInfo;


    static function getAdmin()
    {
        $role = Role::where('name', Role::$admin)->first();

        $admin = self::where('role_name', $role->name)
            ->where('role_id', $role->id)
            ->first();

        return $admin;
    }

    public function isAdmin()
    {
        return $this->role->is_admin;
    }

    public function isUser()
    {
        return $this->role_name === Role::$user;
    }

    public function isTeacher()
    {
        return $this->role_name === Role::$teacher;
    }

    public function isOrganization()
    {
        return $this->role_name === Role::$organization;
    }

    /**
     * Method to check whether User is an Organization Higher Manager
     *
     * @return boolean
     */
    public function isOrganizationManager()
    {
        return $this->role_name === Role::$organization_manager;
    }

    /**
     * Method to check whether User is an Organization Manager
     *
     * @return boolean
     */
    public function isOrganizationSubManager()
    {
        return $this->role_name === Role::$organization_sub_manager;
    }

    /**
     * Method to check whether User is an Organization Consultant
     *
     * @return boolean
     */
    public function isOrganizationStaff()
    {
        return $this->role_name === Role::$organization_staff;
    }

    //organization account itself, or any organization official
    /**
     * Method to check whether User is an Organization Official. This includes the following:
     * 1. Organization
     * 2. Organization Higher Manager
     * 3. Organization Manager
     * 4. Organization Consultant
     *
     * @return boolean
     */public function isOrganizationPersonnel()
    {
        return in_array($this->role_name, [Role::$organization,
            Role::$organization_manager, Role::$organization_sub_manager,
            Role::$organization_staff]);
    }

    //organization account itself, or any organization official
    /**
     * Method to check whether User is an Organization Official but not organization staff. This includes the following:
     * 1. Organization
     * 2. Organization Higher Manager
     * 3. Organization Manager
     *
     * @return boolean
     */
    public function isOrganizationPersonnelButNotStaff()
    {
        return $this->isOrganizationPersonnel() && !$this->isOrganizationStaff();
    }

    //organization account itself, or any organization official
    /**
     * Method to check whether User is an Organization Official but not organization. This includes the following:
     * 1. Consultant
     * 2. Organization Higher Manager
     * 3. Organization Manager
     *
     * @return boolean
     */
    public function isOrganizationMember()
    {
        return $this->isOrganizationPersonnel() && !$this->isOrganization();
    }

    /**
     * Relation to fetch the audit trail against user
     *
     * @return void
     */
    public function auditTrail()
    {
        return $this->hasMany(AuditTrail::class, 'user_id', 'id');
    }

    /**
     * Relation to fetch the Attendance record against user
     *
     * @return void
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function courseLearning()
    {
        return $this->hasMany(CourseLearning::class);
    }

    public function hasPermission($section_name)
    {
        if (self::isAdmin()) {
            if (!isset($this->permissions)) {
                $sections_id = Permission::where('role_id', '=', $this->role_id)->where('allow', true)->pluck('section_id')->toArray();
                $this->permissions = Section::whereIn('id', $sections_id)->pluck('name')->toArray();
            }
            return in_array($section_name, $this->permissions);
        }
        return false;
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function getAvatar()
    {
        if (!empty($this->avatar)) {
            $imgUrl = $this->avatar;
        } else {
            $imgUrl = getPageBackgroundSettings('user_avatar');
        }

        return $imgUrl;
    }

    public function getCover()
    {
        if (!empty($this->cover_img)) {
            $path = str_replace('/storage', '', $this->cover_img);

            $imgUrl = url($path);
        } else {
            $imgUrl = getPageBackgroundSettings('user_cover');
        }

        return $imgUrl;
    }

    public function getProfileUrl()
    {
        return '/users/' . $this->id . '/profile';
    }

    public function getUserGroup()
    {
        if (empty($this->user_group)) {
            if (!empty($this->userGroup) and !empty($this->userGroup->group) and $this->userGroup->group->status == 'active') {
                $this->user_group = $this->userGroup->group;
            }
        }

        return $this->user_group;
    }


    public static function generatePassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function meeting()
    {
        return $this->hasOne('App\Models\Meeting', 'creator_id', 'id');
    }

    public function hasMeeting()
    {
        return Meeting::where('disabled', false)
            ->where('creator_id', $this->id)
            ->first();
    }

    public function ReserveMeetings()
    {
        return $this->hasMany('App\Models\ReserveMeeting', 'user_id', 'id');
    }

    public function affiliateCode()
    {
        return $this->hasOne('App\Models\AffiliateCode', 'user_id', 'id');
    }

    public function followers()
    {
        return Follow::where('user_id', $this->id)->where('status', Follow::$accepted)->get();
    }

    public function following()
    {
        return Follow::where('follower', $this->id)->where('status', Follow::$accepted)->get();
    }

    public function webinars()
    {
        return $this->hasMany('App\Models\Webinar', 'creator_id', 'id')
            ->orWhere('teacher_id', $this->id);
    }

    public function getActiveWebinars($just_count = false)
    {
        $webinars = Webinar::where('status', 'active')
            ->where(function ($query) {
                $query->where('creator_id', $this->id)
                    ->orWhere('teacher_id', $this->id);
            })
            ->orderBy('created_at', 'desc');

        if ($just_count) {
            return $webinars->count();
        }

        return $webinars->get();
    }

    public function userMetas()
    {
        return $this->hasMany('App\Models\UserMeta');
    }

    public function userInfo()
    {
        return $this->hasOne('App\Models\UserInformation', 'user_id', 'id');
    }

    public function carts()
    {
        return $this->hasMany('App\Models\Cart', 'creator_id', 'id');
    }

    public function userGroup()
    {
        return $this->belongsTo('App\Models\GroupUser', 'id', 'user_id');
    }

    public function certificates()
    {
        return $this->hasMany('App\Models\Certificate', 'student_id', 'id');
    }

    public function customBadges()
    {
        return $this->hasMany('App\Models\UserBadge', 'user_id', 'id');
    }

    public function supports()
    {
        return $this->hasMany('App\Models\Support', 'user_id', 'id');
    }

    public function occupations()
    {
        return $this->hasMany('App\Models\UserOccupation', 'user_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\UserDocument', 'user_id', 'id');
    }

    /**
     * getUserDocuments function is defined to get the list of documnets which are
     * visible to the user
     *
     * @return object user documents
     */
    public function getUserDocuments() {
        return $this->hasMany('App\Models\UserDocument', 'user_id', 'id')
        ->where([
            "student_visibility" => true,
        ]);
    }

    public function organization()
    {
        return $this->belongsTo($this, 'organ_id', 'id');
    }

    public function assessedQuizResult()
    {
        return $this->belongsTo('App\Models\QuizzesResult', 'assessed_by', 'id');
    }

    public function getLastLoginAudit()
    {
        if ($this->id > 0) {
            return AuditTrail::where(['user_id' => $this->id, 'audit_type' => AuditTrail::auditType['login']])->orderBy('created_at', "desc")->first();
        } else {
            return null;
        }
    }

    public function profileNotes() {
        return $this->hasMany(ProfileNotes::class, 'user_id', 'id');
    }

    public function getOrganizationTeachers()
    {
        return $this->hasMany($this, 'organ_id', 'id')->where('role_name', Role::$teacher);
    }

    public function getOrganizationStudents()
    {
        return $this->hasMany($this, 'organ_id', 'id')->where('role_name', Role::$user)
            ->with('manager');
    }

    /**
     * Return Students against Organization Site(s) of requesting user.
     * In case of organization, students from all sites are returned
     * Whereas, in case of members of organizations, students from their associated sites are returned
     *
     * Note: Method returns Query not collection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOrganizationSiteStudents()
    {
        $students = null;
        // if (isset($this->role_name) && in_array($this->role_name, [Role::$organization, Role::$organization_manager, Role::$organization_sub_manager, Role::$organization_staff])) {
        if (isset($this->role_name) && $this->isOrganizationPersonnel()) {
            if ($this->role_name === Role::$organization) {
                //get All students against Organization
                $students = User::where(['role_name'=> Role::$user, 'organ_id' => $this->id])->with('organizationSites', 'manager');
            } else {
                //get Students visible to this manager/submanager/consultant
                $managerSites = $this->organizationSitesArray();
                if ($this->isOrganizationStaff()) {
                    $students = User::select('users.*')->where(['role_name' => Role::$user, 'manager_id' => $this->id])->with('organizationSites', 'manager')
                                ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                                ->whereIn('organization_site_user.site_id', $managerSites);
                } else {
                    $students = User::select('users.*')->where('role_name', Role::$user)->with('organizationSites', 'manager')
                                ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                                ->whereIn('organization_site_user.site_id', $managerSites);
                }
            }
        }
        return $students;
    }

    public function getOrganizationManagers()
    {
        return $this->hasMany($this, 'organ_id', 'id')->where('role_name', Role::$organization_manager);
    }

    /* Returns a query - not a collection!!! */
    public function getOrganizationSubManagers()
    {
        $subManagers = null;
        if (isset($this->role_name) && in_array($this->role_name, [Role::$organization, Role::$organization_manager])) {
            if ($this->role_name === Role::$organization) {
                //get All SubManagers against Organization
                $subManagers = User::where(['role_name' => Role::$organization_sub_manager, 'organ_id' => $this->id])->with('organizationSites');
            } else {
                //get SubManager visible to this manager
                $managerSites = $this->organizationSitesArray();
                $subManagers = User::select('users.*')->where('role_name', Role::$organization_sub_manager)->with('organizationSites')
                            ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                            ->whereIn('organization_site_user.site_id', $managerSites);
            }
        }
        return $subManagers;
    }

    /**
     * Returns staff against organization
     * If requesting user is Organization itself, all Organization Staff/Consultants are shown.
     * If requesting user is a Higher Manager/Manager, only Staff from their associated sites are shown.
     *
     * Note: Returns Query - not a collection!!!
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOrganizationStaff() : \Illuminate\Database\Eloquent\Builder
    {
        $consultants = null;
        if (isset($this->role_name) && in_array($this->role_name, [Role::$organization, Role::$organization_manager, Role::$organization_sub_manager])) {
            if ($this->role_name === Role::$organization) {
                //get All consultants against Organization
                $consultants = User::where(['role_name' => Role::$organization_staff, 'organ_id' => $this->id])->with('organizationSites');
            } else {
                //get Consultant visible to this manager/submanager
                $managerSites = $this->organizationSitesArray();
                $consultants = User::select('users.*')->where('role_name', Role::$organization_staff)->with('organizationSites')
                            ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                            ->whereIn('organization_site_user.site_id', $managerSites);
            }
        }
        return $consultants;
    }

    /* public function site()
    {
        //return OrganizationSite::where(['organ_id', $this->organ_id, 'user_id', $this->id])->first();
        return $this->belongsToMany(OrganizationSite::class, 'organization_site_user', 'site_id', 'user_id');
    } */

    public function organizationSites()
    {
        return $this->belongsToMany(OrganizationSite::class, 'organization_site_user', 'user_id', 'site_id');
    }

    /**
     * Returns list of Organization Site Ids (the user is associated with) as array
     *
     * @return array
     */
    public function organizationSitesArray()
    {
        $organizationSites =  $this->organizationSites;
        $organizationSitesArray = [];
        foreach($organizationSites as $organizationSite) {
            $organizationSitesArray[] = $organizationSite->pivot->site_id;
        }
        return $organizationSitesArray;
    }

    //public function


    /* students inactivity email notifications settings */
    public function inActivityEmailSettings()
    {
        return $this->hasOne(StudentNotificationSetting::class, 'organ_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo($this, 'manager_id', 'id');
    }

    public function getManagerStudents()
    {
        return $this->hasMany($this, 'manager_id', 'id')->where('role_name', Role::$user);
    }

    /**
     * Function that utilizes the getOrganizationStudents relationship,
     * and returns a filtered collection of students that have fallen behind
     *
     * @return Illuminate\Support\Collection
     */
    public function getOrganizationStudentsBehindProgress()
    {
        if ($this->isOrganization()) {
            $students = $this->getOrganizationStudents;
            $collection = collect($students);
        } else if ($this->isOrganizationPersonnel()) {
            $students = $this->getOrganizationSiteStudents();
            $collection = $students->get();
        }
        return $collection->filter(function($user){
                    return $user->isBehindProgress();
                });
    }


    public function getTeacherStudents($org_id)
    {
        return $this->where('role_name', Role::$user)->where('organ_id', $org_id);
    }

    public function zoomApi()
    {
        return $this->hasOne('App\Models\UserZoomApi', 'user_id', 'id');
    }


    public function rates()
    {
        $webinars = $this->webinars()
            ->where('status', 'active')
            ->get();

        $rate = 0;

        if (!empty($webinars)) {
            $rates = 0;
            $count = 0;

            foreach ($webinars as $webinar) {
                $webinarRate = $webinar->getRate();

                if (!empty($webinarRate) and $webinarRate > 0) {
                    $count += 1;
                    $rates += $webinarRate;
                }
            }

            if ($rates > 0) {
                if ($count < 1) {
                    $count = 1;
                }

                $rate = number_format($rates / $count, 2);
            }
        }

        return $rate;
    }

    public function reviewsCount()
    {
        $webinars = $this->webinars;
        $count = 0;

        if (!empty($webinars)) {
            foreach ($webinars as $webinar) {
                $count += $webinar->reviews->count();
            }
        }

        return $count;
    }

    public function getBadges($customs = true, $getNext = false)
    {
        return Badge::getUserBadges($this, $customs, $getNext);
    }

    public function getCommission()
    {
        $commission = 0;
        $financialSettings = getFinancialSettings();

        if (!empty($financialSettings) and !empty($financialSettings['commission'])) {
            $commission = (int)$financialSettings['commission'];
        }

        $getUserGroup = $this->getUserGroup();
        if (!empty($getUserGroup) and isset($getUserGroup->commission)) {
            $commission = $getUserGroup->commission;
        }

        if (!empty($this->commission)) {
            $commission = $this->commission;
        }

        return $commission;
    }

    public function getIncome()
    {
        $totalIncome = Accounting::where('user_id', $this->id)
            ->where('type_account', Accounting::$income)
            ->where('type', Accounting::$addiction)
            ->where('system', false)
            ->where('tax', false)
            ->sum('amount');

        return $totalIncome;
    }

    public function getPayout()
    {
        $credit = Accounting::where('user_id', $this->id)
            ->where('type_account', Accounting::$income)
            ->where('type', Accounting::$addiction)
            ->where('system', false)
            ->where('tax', false)
            ->sum('amount');

        $debit = Accounting::where('user_id', $this->id)
            ->where('type_account', Accounting::$income)
            ->where('type', Accounting::$deduction)
            ->where('system', false)
            ->where('tax', false)
            ->sum('amount');

        return $credit - $debit;
    }

    public function getAccountingCharge()
    {
        $query = Accounting::where('user_id', $this->id)
            ->where('type_account', Accounting::$asset)
            ->where('system', false)
            ->where('tax', false);

        $additions = deepClone($query)->where('type', Accounting::$addiction)
            ->sum('amount');

        $deductions = deepClone($query)->where('type', Accounting::$deduction)
            ->sum('amount');

        $charge = $additions - $deductions;
        return $charge > 0 ? $charge : 0;
    }

    public function getAccountingBalance()
    {
        $additions = Accounting::where('user_id', $this->id)
            ->where('type', Accounting::$addiction)
            ->where('system', false)
            ->where('tax', false)
            ->sum('amount');

        $deductions = Accounting::where('user_id', $this->id)
            ->where('type', Accounting::$deduction)
            ->where('system', false)
            ->where('tax', false)
            ->sum('amount');

        $balance = $additions - $deductions;
        return $balance > 0 ? $balance : 0;
    }

    public function getPurchaseAmounts()
    {
        return Sale::where('buyer_id', $this->id)
            ->sum('amount');
    }

    public function getSaleAmounts()
    {
        return Sale::where('seller_id', $this->id)
            ->sum('amount');
    }

    public function sales()
    {
        $webinarIds = Webinar::where('creator_id', $this->id)->pluck('id')->toArray();

        return Sale::whereIn('webinar_id', $webinarIds)->sum('amount');
    }

    public function salesCount()
    {
        $webinarIds = $this->webinars()->pluck('id')->toArray();

        return Sale::whereIn('webinar_id', $webinarIds)->count();
    }

    public function getUnReadNotifications()
    {
        $notifications = Notification::where(function ($query) {
            $query->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->where('type', 'single');
            })->orWhere(function ($query) {
                if (!$this->isAdmin()) {
                    $query->whereNull('user_id')
                        ->whereNull('group_id')
                        ->where('type', 'all_users');
                }
            });
        })->doesntHave('notificationStatus')
            ->orderBy('created_at', 'desc')
            ->get();

        $userGroup = $this->userGroup()->first();
        if (!empty($userGroup)) {
            $groupNotifications = Notification::where('group_id', $userGroup->group_id)
                ->where('type', 'group')
                ->doesntHave('notificationStatus')
                ->orderBy('created_at', 'desc')
                ->get();

            if (!empty($groupNotifications) and !$groupNotifications->isEmpty()) {
                $notifications = $notifications->merge($groupNotifications);
            }
        }

        if ($this->isUser()) {
            $studentsNotifications = Notification::whereNull('user_id')
                ->whereNull('group_id')
                ->where('type', 'students')
                ->doesntHave('notificationStatus')
                ->orderBy('created_at', 'desc')
                ->get();
            if (!empty($studentsNotifications) and !$studentsNotifications->isEmpty()) {
                $notifications = $notifications->merge($studentsNotifications);
            }
        }

        if ($this->isTeacher()) {
            $instructorNotifications = Notification::whereNull('user_id')
                ->whereNull('group_id')
                ->where('type', 'instructors')
                ->doesntHave('notificationStatus')
                ->orderBy('created_at', 'desc')
                ->get();
            if (!empty($instructorNotifications) and !$instructorNotifications->isEmpty()) {
                $notifications = $notifications->merge($instructorNotifications);
            }
        }

        if ($this->isOrganization()) {
            $organNotifications = Notification::whereNull('user_id')
                ->whereNull('group_id')
                ->where('type', 'organizations')
                ->doesntHave('notificationStatus')
                ->orderBy('created_at', 'desc')
                ->get();
            if (!empty($organNotifications) and !$organNotifications->isEmpty()) {
                $notifications = $notifications->merge($organNotifications);
            }
        }

        return $notifications;
    }

    public function getUnreadNoticeboards()
    {
        $noticeboards = Noticeboard::where(function ($query) {
            $query->whereNotNull('organ_id')
                ->where('organ_id', $this->organ_id)
                ->where(function ($query) {
                    if ($this->isOrganization()) {
                        $query->where('type', 'organizations');
                    } else {
                        $type = 'students';

                        if ($this->isTeacher()) {
                            $type = 'instructors';
                        }

                        $query->whereIn('type', ['students_and_instructors', $type]);
                    }
                });
        })->orWhere(function ($query) {
            $type = ['all'];

            if ($this->isUser()) {
                $type = array_merge($type, ['students', 'students_and_instructors']);
            } elseif ($this->isTeacher()) {
                $type = array_merge($type, ['instructors', 'students_and_instructors']);
            } elseif ($this->isOrganization()) {
                $type = array_merge($type, ['organizations']);
            }

            $query->whereNull('organ_id')
                ->whereIn('type', $type);
        })->orderBy('created_at', 'desc')
            ->get();


        /*
        ->whereDoesntHave('noticeboardStatus', function ($qu) {
            $qu->where('user_id', $this->id);
        })
        */

        return $noticeboards;
    }

    public function getPurchasedCoursesIds()
    {
        $webinarIds = [];

        $sales = Sale::where('buyer_id', $this->id)
            ->whereNotNull('webinar_id')
            ->where('type', 'webinar')
            ->whereNull('refund_at')
            ->get();

        foreach ($sales as $sale) {
            if ($sale->payment_method == Sale::$subscribe) {
                $subscribe = $sale->getUsedSubscribe($sale->buyer_id, $sale->webinar_id);

                if (!empty($subscribe)) {
                    $subscribeSale = Sale::where('buyer_id', $this->id)
                        ->where('type', Sale::$subscribe)
                        ->where('subscribe_id', $subscribe->id)
                        ->whereNull('refund_at')
                        ->latest('created_at')
                        ->first();

                    if (!empty($subscribeSale)) {
                        $usedDays = (int)diffTimestampDay(time(), $subscribeSale->created_at);
                        if ($usedDays <= $subscribe->days) {
                            $webinarIds[] = $sale->webinar_id;
                        }
                    }
                }
            } else {
                $webinarIds[] = $sale->webinar_id;
            }
        }

        return $webinarIds;
    }

    public function getPurchasedCourses()
    {
        /* $webinarIds = $this->getPurchasedCoursesIds();
        $webinars = Webinar::where('user_id', $this->id)
                    ->whereIn('webinar_id', $webinarIds)
                    ->get();
        return $webinars; */
        $purchases = Sale::where('buyer_id', $this->id)->whereNull('refund_at')
                ->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id')->with(['webinar',
                'referenceSale'])->get();
        return $purchases;
    }

    public function isBehindProgress()
    {
        $classesPurchased = $this->getPurchasedCourses();
        $behindProgress = false;
        if (isset($classesPurchased) && count($classesPurchased)) {
            foreach ($classesPurchased as $classPurchased) {
                if ($classPurchased->webinar->getProgress($this->id) < $classPurchased->webinar->getExpectedProgress($this->id)) {
                    $behindProgress = true;
                }
            }
        }
        return $behindProgress;
    }

    public function getActiveQuizzesResults($group_by_quiz = false, $status = null)
    {
        $query = QuizzesResult::where('user_id', $this->id);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($group_by_quiz) {
            $query->groupBy('quiz_id');
        }

        return $query->get();
    }

    /**
     * Function to return query builder instance for pending assessments accessible to requesting teacher or admin
     *
     * Note: Entertained roles are only: [Admin, Teacher]. Otherwise an empty collection is returned
     *
     * @return Illuminate\Database\Eloquent\Builder|Illuminate\Support\Collection
     */
    public function getAccessiblePendingAssessments()
    {
        $quizzesResults = collect();
        if ($this->isTeacher()) {
            $ownCourses = Webinar::where('teacher_id', $this->id)->get()->pluck('id')->toArray();
            $invitedCourses = WebinarPartnerTeacher::where('teacher_id', $this->id)->get()->pluck('webinar_id')->toArray();
            $courses = array_merge($ownCourses, $invitedCourses);
            //Fetch all QuizIds
            $quizIds = Quiz::whereIn('webinar_id', $courses)->get()->pluck('id')->toArray();
            $quizzesResults = QuizzesResult::whereIn('quiz_id', $quizIds)
                ->where('status', "".QuizzesResult::$waiting."")
                ->with([
                    'quiz' => function ($query) {
                        $query->with(['teacher'=> function($query) {
                            $query->with(['organization']);
                        }]);
                    },
                    'user'
                ])
                ->orderBy('created_at', 'desc');
        } elseif ($this->isAdmin()) {
            $quizzesResults = QuizzesResult::where('status', QuizzesResult::$waiting)
            ->with([
                'quiz' => function ($query) {
                    $query->with(['teacher'=> function($query) {
                        $query->with(['organization']);
                    }]);
                },
                'user'
            ])
            ->orderBy('created_at', 'desc');
        }
        return $quizzesResults;
    }

    /**
     * Function to return Profile Completion step in case of missing user enrolment information
     *
     * @param int $userId
     * @return int
     */
    public function getMissingUserEnrolmentStep($userId = null)
    {
        $step = -1;
        $user = null;
        if (isset($userId) && (int)$userId > 0) {
            $user = User::find((int)$userId);
        } elseif (isset($this->id) && (int)$this->id > 0) {
            $user = User::find((int)$this->id);
        }
        $userInfo = (isset($user)) ? $user->userInfo : $this->userInfo;

        // $usiSupportingDoc = UserDocument::where(['user_id' => $user->id,
        //     'title' => '100 Points Supporting Document'])->exists();
        $usiSupportingDoc = UserDocument::where(['user_id' => $user->id])->exists();

        $studentDeclaration = StudentDeclaration::where('user_id', $user->id)->exists();

        if (
            empty($userInfo->title) || empty($userInfo->first_name) || empty($userInfo->sur_name)
            || empty($userInfo->gender) || empty($user->address) || empty($userInfo->suburb)
            || empty($userInfo->state) || empty($userInfo->post_code) || empty($userInfo->emergency_contact)
            || empty($userInfo->contact_number)
        ) {
            $step = 3;
        } else if (
            empty($userInfo->cultural_identity) || empty($userInfo->birth_country) || empty($userInfo->birth_city)
            || empty($userInfo->citizenship)
        ) {
            $step = 4;
        } else if ( isset($userInfo->does_speak_other_language) && empty($userInfo->other_language)) {
            $step = 5;
        } else if ( empty($userInfo->employment_type)) {
            $step = 6;
        } else if (
            empty($userInfo->school_level)
            || (isset($userInfo->school_level)
                && in_array($userInfo->school_level, [1,2,3,4,5])
                && empty($userInfo->school_completed_year)
            )
            || (isset($userInfo->is_enrolled) && $userInfo->is_enrolled && empty($userInfo->enrolled_studies))
        ) {
            $step = 7;
        } else if ( empty($userInfo->study_reason)) {
            $step = 8;
        } else if (
            (empty($userInfo->can_gaps_search_usi) && empty($userInfo->rto_permission))
            || (!empty($userInfo->rto_permission) && !$usiSupportingDoc)
        ) {
            $step = 9;
        } else if ( !$studentDeclaration) {
            $step = 10;
        }

        return $step;
    }

    public function breaks()
    {
        return $this->hasMany(UserBreak::class);
    }

    public function approvedBreaks()
    {
        return $this->hasMany(UserBreak::class)
            ->where('status', UserBreak::$status['approved']);
    }

    /**
     * Relation that returns breaks against a user that are in one of the following states:
     * approved or pending
     * Slots against these breaks should not be reuseable. Canceled or rejected slots may be reused.
     */

    public function occupiedBreaks()
    {
        return $this->hasMany(UserBreak::class)
            ->whereIn('status', [UserBreak::$status['approved'], UserBreak::$status['pending']]);
    }

    public function placementNotes()
    {
        return $this->hasMany(placementNotes::class);
    }

    /**
     * Method to fetch upaid courses by user (if any)
     * If no courses are pending payment or user is not enrolled in any courses, the method returns an empty array instead.
     * @return array
     */
    public function getPendingPayments()
    {
        $pendingPaymentCourses = [];
        $courseSales = $this->getPurchasedCourses();
        $paymentStatuses = array_flip(Sale::$paymentStatus); //flipping keys with values

        if ($courseSales) {
            foreach($courseSales as $course) {
                if (!empty($course->refunded_at)) {
                    continue;
                }
                //was the sale 'paid for' by another user (e.g., organization)?
                if (empty($course->self_payed) && !empty($course->referenceSale)) {
                    $referenceSale = $course->referenceSale;
                    if ($referenceSale->payment_status === Sale::$paymentStatus['refunded']) {
                        continue;
                    } elseif ($referenceSale->payment_status !== Sale::$paymentStatus['paid']) {
                        //cases that remain are either, pending or pushed to xero
                        $pendingPaymentCourses[] = [
                            'webinar'           =>  $course->webinar->title,
                            'amount'            =>  $referenceSale->total_amount,
                            'date_purchased'    =>  date('d-M-Y', $referenceSale->created_at),
                            'payment_status'    =>  $paymentStatuses[$referenceSale->payment_status],
                        ];
                    }
                } elseif (!empty($course->self_payed) && (int)$course->self_payed === 1) { //self-payed course
                    if ($course->payment_status === Sale::$paymentStatus['refunded']) {
                        continue;
                    } elseif ($course->payment_status !== Sale::$paymentStatus['paid']) {
                        //cases that remain are either, pending or pushed to xero
                        $pendingPaymentCourses[] = [
                            'webinar'           =>  $course->webinar->title,
                            'amount'            =>  $course->total_amount,
                            'date_purchased'    =>  date('d-M-Y', $course->created_at),
                            'payment_status'    =>  $paymentStatuses[$course->payment_status],
                        ];
                    }
                }
            }
        }
        return $pendingPaymentCourses;
    }


    public function canDeleteStudent(){
        // only admin can delete students
        if($this->isAdmin()){

            return true;
        }

        return false;
    }

    public static function newRandomPassword($length=10){

        return Str::random($length);
    }

    /**
     * getStudentProgress function is defined to get the student's progress including [actual progress, expected progress]
     *
     * @return array expected progress and actual progress of the student
     */
    public function getStudentProgress() {
        $student = User::find( $this->id );
        $classesPurchased = $student->getPurchasedCourses();
        if (isset($classesPurchased) && count($classesPurchased)) {
            $studentProgress = 0;
            $expectedProgress = 0;
            foreach ($classesPurchased as $classPurchased) {
                $studentProgress = $studentProgress + $classPurchased->webinar->getProgress($student->id);
                $expectedProgress = $expectedProgress + $classPurchased->webinar->getExpectedProgress($student->id);
            }

            $actualProgress = round($studentProgress / count($classesPurchased), 2);
            $expectedProgress = round($expectedProgress / count($classesPurchased), 2);
            $return = [
                "actualProgress" => $actualProgress,
                "expectedProgress" => $expectedProgress,
            ];
            return $return;
        } else {
            $return = [
                "actualProgress" => 0,
                "expectedProgress" => 0,
            ];
            return $return;
        }
    }

}
