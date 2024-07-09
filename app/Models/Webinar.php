<?php

namespace App\Models;

use App\User;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Jorenvh\Share\ShareFacade;
use Spatie\CalendarLinks\Link;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Webinar extends Model implements TranslatableContract
{
    use Translatable;
    use Sluggable;

    protected $table = 'webinars';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $active = 'active';
    static $pending = 'pending';
    static $isDraft = 'is_draft';
    static $inactive = 'inactive';

    static $webinar = 'webinar';
    static $course = 'course';
    static $textLesson = 'text_lesson';

    static $quiz = 'quiz';

    static $statuses = [
        'active', 'pending', 'is_draft', 'inactive'
    ];

    static $videoDemoSource = ['upload', 'youtube', 'vimeo', 'external_link'];

    public $translatedAttributes = ['title', 'description', 'seo_description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }

    public function getSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_description');
    }

    public function getPriceAttribute()
    {
        $result = $this->attributes['price'] ?? null;
        $user = auth()->user();
        if (!empty($this->attributes['organization_price']) and !empty($user) and $this->creator->isOrganization() and $user->organ_id == $this->creator_id) {
            $result = $this->attributes['organization_price'];
        }
        return $result;
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id')->withTrashed();
    }

    public function teacher()
    {
        return $this->belongsTo('App\User', 'teacher_id', 'id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function filterOptions()
    {
        return $this->hasMany('App\Models\WebinarFilterOption', 'webinar_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket', 'webinar_id', 'id');
    }


    public function chapters()
    {
        return $this->hasMany('App\Models\WebinarChapter', 'webinar_id', 'id');
    }

    public function sessions()
    {
        return $this->hasMany('App\Models\Session', 'webinar_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\File', 'webinar_id', 'id');
    }

    public function assignments()
    {
        return $this->hasMany('App\Models\WebinarAssignment', 'webinar_id', 'id');
    }

    public function textLessons()
    {
        return $this->hasMany('App\Models\TextLesson', 'webinar_id', 'id');
    }

    public function faqs()
    {
        return $this->hasMany('App\Models\Faq', 'webinar_id', 'id');
    }

    public function webinarExtraDescription()
    {
        return $this->hasMany('App\Models\WebinarExtraDescription', 'webinar_id', 'id');
    }

    public function prerequisites()
    {
        return $this->hasMany('App\Models\Prerequisite', 'webinar_id', 'id');
    }

    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'webinar_id', 'id');
    }

    public function webinarPartnerTeacher()
    {
        return $this->hasMany('App\Models\WebinarPartnerTeacher', 'webinar_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany('App\Models\Tag', 'webinar_id', 'id');
    }

    public function purchases()
    {
        return $this->hasMany('App\Models\Purchase', 'webinar_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'webinar_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\WebinarReview', 'webinar_id', 'id');
    }

    public function sales()
    {
        return $this->hasMany('App\Models\Sale', 'webinar_id', 'id')
            ->whereNull('refund_at')
            ->where('type', 'webinar');
    }

    public function feature()
    {
        return $this->hasOne('App\Models\FeatureWebinar', 'webinar_id', 'id');
    }

    public function noticeboards()
    {
        return $this->hasMany('App\Models\CourseNoticeboard', 'webinar_id', 'id');
    }

    public function forums()
    {
        return $this->hasMany('App\Models\CourseForum', 'webinar_id', 'id');
    }

    public function getRate()
    {
        $rate = 0;

        if (!empty($this->avg_rates)) {
            $rate = $this->avg_rates;
        } else {
            $reviews = $this->reviews()
                ->where('status', 'active')
                ->get();

            if (!empty($reviews) and $reviews->count() > 0) {
                $rate = number_format($reviews->avg('rates'), 2);
            }
        }


        if ($rate > 5) {
            $rate = 5;
        }

        return $rate > 0 ? number_format($rate, 2) : 0;
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function makeSlug($title)
    {
        return SlugService::createSlug(self::class, 'slug', $title);
    }

    public function bestTicket($with_percent = false)
    {
        $ticketPercent = 0;
        $bestTicket = $this->price;

        $activeSpecialOffer = $this->activeSpecialOffer();

        if ($activeSpecialOffer) {
            $bestTicket = $this->price - ($this->price * $activeSpecialOffer->percent / 100);
            $ticketPercent = $activeSpecialOffer->percent;
        } else {
            foreach ($this->tickets as $ticket) {

                if ($ticket->isValid()) {
                    $discount = $this->price - ($this->price * $ticket->discount / 100);

                    if ($bestTicket > $discount) {
                        $bestTicket = $discount;
                        $ticketPercent = $ticket->discount;
                    }
                }
            }
        }

        if ($with_percent) {
            return [
                'bestTicket' => $bestTicket,
                'percent' => $ticketPercent
            ];
        }

        return $bestTicket;
    }

    public function getDiscount($ticket = null, $user = null)
    {
        $activeSpecialOffer = $this->activeSpecialOffer();

        $discountOut = $activeSpecialOffer ? $this->price * $activeSpecialOffer->percent / 100 : 0;

        if (!empty($user) and !empty($user->getUserGroup()) and isset($user->getUserGroup()->discount) and $user->getUserGroup()->discount > 0) {
            $discountOut += $this->price * $user->getUserGroup()->discount / 100;
        }

        if (!empty($ticket) and $ticket->isValid()) {
            $discountOut += $this->price * $ticket->discount / 100;
        }

        return $discountOut;
    }

    public function getDiscountPercent()
    {
        $percent = 0;

        $activeSpecialOffer = $this->activeSpecialOffer();

        if (!empty($activeSpecialOffer)) {
            $percent += $activeSpecialOffer->percent;
        }

        $tickets = Ticket::where('webinar_id', $this->id)->get();

        foreach ($tickets as $ticket) {
            if (!empty($ticket) and $ticket->isValid()) {
                $percent += $ticket->discount;
            }
        }

        return $percent;
    }

    public function getWebinarCapacity()
    {
        $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

        $capacity = $this->capacity - $salesCount;

        return $capacity > 0 ? $capacity : 0;
    }

    public function getExpiredAccessDays($purchaseDate, $giftId = null)
    {
        if (!empty($giftId)) {
            $gift = Gift::query()->where('id', $giftId)
                ->where('status', 'active')
                ->first();
            if (!empty($gift) and !empty($gift->date)) {
                $purchaseDate = $gift->date;
            }
        }
        return strtotime("+{$this->access_days} days", $purchaseDate);
    }

    public function checkHasExpiredAccessDays($purchaseDate, $giftId = null)
    {
        // true => has access
        // false => not access (expired)
        if (!empty($giftId)) {
            $gift = Gift::query()->where('id', $giftId)
                ->where('status', 'active')
                ->first();
            if (!empty($gift) and !empty($gift->date)) {
                $purchaseDate = $gift->date;
            }
        }
        $time = time();
        return strtotime("+{$this->access_days} days", $purchaseDate) > $time;
    }

    public function getSaleItem($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }
        if (!empty($user)) {
            // return Sale::where('buyer_id', $user->id)
            return Sale::query()->where('buyer_id', $user->id)
                ->where('webinar_id', $this->id)
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                // these were the new files added in the rocketlms update
                // ->where('access_to_purchased_item', true)
                // ->orderBy('created_at', 'desc')
                ->first();
        }
        return null;
    }

    // public function checkUserHasBought($user = null)
    public function checkUserHasBought($user = null, $checkExpired = true, $test = false): bool
    {
        $hasBought = false;
        if (empty($user) and auth()->check()) {
            $user = auth()->user();
        }

        if (!empty($user)) {
            /* $sale = Sale::where('buyer_id', $user->id)
                ->where('webinar_id', $this->id)
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->first(); */
            $sale = $this->getSaleItem($user);
            if (!empty($sale)) {
                $hasBought = true;

                if ($sale->payment_method == Sale::$subscribe) {
                    $subscribe = $sale->getUsedSubscribe($sale->buyer_id, $sale->webinar_id);

                    if (!empty($subscribe)) {
                        $subscribeSaleCreatedAt = null;
                        if (!empty($subscribe->installment_order_id)) {
                            $installmentOrder = InstallmentOrder::query()->where('user_id', $user->id)
                                ->where('id', $subscribe->installment_order_id)
                                ->where('status', 'open')
                                ->whereNull('refund_at')
                                ->first();
                            if (!empty($installmentOrder)) {
                                $subscribeSaleCreatedAt = $installmentOrder->created_at;
                                if ($installmentOrder->checkOrderHasOverdue()) {
                                    $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days');
                                    if (empty($overdueIntervalDays) or $installmentOrder->overdueDaysPast() > $overdueIntervalDays) {
                                        $hasBought = false;
                                    }
                                }
                            }
                        } else {
                            $subscribeSale = Sale::where('buyer_id', $user->id)
                                ->where('type', Sale::$subscribe)
                                ->where('subscribe_id', $subscribe->id)
                                ->whereNull('refund_at')
                                ->latest('created_at')
                                ->first();
                            if (!empty($subscribeSale)) {
                                $subscribeSaleCreatedAt = $subscribeSale->created_at;
                            }
                        }
                        if (!empty($subscribeSaleCreatedAt)) {
                            $usedDays = (int)diffTimestampDay(time(), $subscribeSaleCreatedAt);

                            if ($usedDays > $subscribe->days) {
                                $hasBought = false;
                            }
                        }
                    } else {
                        $hasBought = false;
                    }
                }

                if ($hasBought and !empty($this->access_days) and $checkExpired) {
                    $hasBought = $this->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id);
                }
            }
            if (!$hasBought) {
                $hasBought = ($this->creator_id == $user->id or $this->teacher_id == $user->id);
                if (!$hasBought) {
                    $partnerTeachers = !empty($this->webinarPartnerTeacher) ? $this->webinarPartnerTeacher->pluck('teacher_id')->toArray() : [];
                    $hasBought = in_array($user->id, $partnerTeachers);
                }
            }
            if (!$hasBought) {
                $hasBought = $user->isAdmin();
            }
            if (!$hasBought) {
                $bundleWebinar = BundleWebinar::where('webinar_id', $this->id)
                    ->with([
                        'bundle'
                    ])->get();
                if ($bundleWebinar->isNotEmpty()) {
                    foreach ($bundleWebinar as $item) {
                        if (!empty($item->bundle) and $item->bundle->checkUserHasBought($user)) {
                            $hasBought = true;
                        }
                    }
                }
            }
            /* Check Installment */
            if (!$hasBought) {
                $installmentOrder = $this->getInstallmentOrder();
                if (!empty($installmentOrder)) {
                    $hasBought = true;
                    if ($installmentOrder->checkOrderHasOverdue()) {
                        $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days');
                        if (empty($overdueIntervalDays) or $installmentOrder->overdueDaysPast() > $overdueIntervalDays) {
                            $hasBought = false;
                        }
                    }
                }
            }
            /* Check Gift */
            if (!$hasBought) {
                $gift = Gift::query()->where('email', $user->email)
                    ->where('status', 'active')
                    ->where('webinar_id', $this->id)
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->first();
                if (!empty($gift)) {
                    $hasBought = true;
                }
            }
        }
        return $hasBought;
    }

    public function customCheckUserHasBought($userId = null)
    {
        $hasBought = false;

        $sale = Sale::where('buyer_id', $userId)
                ->where('webinar_id', $this->id)
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->first();

        if (!empty($sale)) {
            $hasBought = true;

            if ($sale->payment_method == Sale::$subscribe) {
                    $subscribe = $sale->getUsedSubscribe($sale->buyer_id, $sale->webinar_id);

                    if (!empty($subscribe)) {
                        $subscribeSale = Sale::where('buyer_id', $userId)
                            ->where('type', Sale::$subscribe)
                            ->where('subscribe_id', $subscribe->id)
                            ->whereNull('refund_at')
                            ->latest('created_at')
                            ->first();

                        if (!empty($subscribeSale)) {
                            $usedDays = (int)diffTimestampDay(time(), $subscribeSale->created_at);

                            if ($usedDays > $subscribe->days) {
                                $hasBought = false;
                            }
                        }
                    } else {
                        $hasBought = false;
                    }
                }
        }

        return $hasBought;
    }

    public function getInstallmentOrder()
    {
        $user = auth()->user();
        if (!empty($user)) {
            return InstallmentOrder::query()->where('user_id', $user->id)
                ->where('webinar_id', $this->id)
                ->where('status', 'open')
                ->whereNull('refund_at')
                ->first();
        }
        return null;
    }

    public function getFilesLearningProgressStat($userId = null)
    {
        $passed = 0;
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $files = $this->files()
            ->where('status', 'active')
            ->get();
        foreach ($files as $file) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('file_id', $file->id)
                ->first();
            if (!empty($status)) {
                $passed += 1;
            }
        }
        return [
            'passed' => $passed,
            'count' => count($files)
        ];
    }

    public function getSessionsLearningProgressStat($userId = null)
    {
        $passed = 0;
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $sessions = $this->sessions()
            ->where('status', 'active')
            ->get();
        foreach ($sessions as $session) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('session_id', $session->id)
                ->first();
            if (!empty($status)) {
                $passed += 1;
            }
        }
        return [
            'passed' => $passed,
            'count' => count($sessions)
        ];
    }

    public function getTextLessonsLearningProgressStat($userId = null)
    {
        $passed = 0;
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $textLessons = $this->textLessons()
            ->where('status', 'active')
            ->get();
        foreach ($textLessons as $textLesson) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('text_lesson_id', $textLesson->id)
                ->first();
            if (!empty($status)) {
                $passed += 1;
            }
        }
        return [
            'passed' => $passed,
            'count' => count($textLessons)
        ];
    }

    public function getAssignmentsLearningProgressStat($userId = null)
    {
        $passed = 0;
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $assignments = $this->assignments()
            ->where('status', 'active')
            ->get();
        foreach ($assignments as $assignment) {
            $assignmentHistory = WebinarAssignmentHistory::where('assignment_id', $assignment->id)
                ->where('student_id', $userId)
                ->where('status', WebinarAssignmentHistory::$passed)
                ->first();
            if (!empty($assignmentHistory)) {
                $passed += 1;
            }
        }
        return [
            'passed' => $passed,
            'count' => count($assignments)
        ];
    }

    public function getQuizzesLearningProgressStat($userId = null)
    {
        $passed = 0;
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $quizzes = $this->quizzes()
            ->where('status', 'active')
            ->get();
        foreach ($quizzes as $quiz) {
            $quizHistory = QuizzesResult::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->where('status', QuizzesResult::$passed)
                ->first();
            if (!empty($quizHistory)) {
                $passed += 1;
            }
        }
        return [
            'passed' => $passed,
            'count' => count($quizzes)
        ];
    }

    /* This is the native method used by RocketLMS to getProgress - released in the new version */
    // public function getProgress($isLearningPage = false , $user_id = -1)
    public function getProgress($user_id = -1)
    {
        $progress = 0;
        //if the user is admin then the user_id will be -1 and no user found
        $user = User::find($user_id);

        //commented the following if condition
        // (
        //     auth()->check() and
        //     $this->checkUserHasBought() and
        //     (
        //         !$this->isWebinar() or
        //         ($this->isWebinar() and $this->isProgressing()) or
        //         $isLearningPage
        //     )
        // )
        if (
            auth()->check() and
            $this->checkUserHasBought($user) and
            (
                !$this->isWebinar() or
                ($this->isWebinar() and $this->isProgressing())
            )
        ) {

            // $user = null;
            // if ($user_id <= 0) {
            //     $user = auth()->user();
            //     //if admin
            //     $user_id = -1;
            // } else {
            //     $user = User::find($user_id);
            // }
            // $user_id = auth()->id();

            // if the user is admin then the user_id will be -1 and no user found
            // $user = User::find($user_id);
            if (! $user) {
                return $progress;
            }
            $filesStat = $this->getFilesLearningProgressStat($user_id);
            $sessionsStat = $this->getSessionsLearningProgressStat($user_id);
            $textLessonsStat = $this->getTextLessonsLearningProgressStat($user_id);
            $assignmentsStat = $this->getAssignmentsLearningProgressStat($user_id);
            $quizzesStat = $this->getQuizzesLearningProgressStat($user_id);
            $passed = $filesStat['passed'] + $sessionsStat['passed'] + $textLessonsStat['passed'] + $assignmentsStat['passed'] + $quizzesStat['passed'];
            $count = $filesStat['count'] + $sessionsStat['count'] + $textLessonsStat['count'] + $assignmentsStat['count'] + $quizzesStat['count'];
            if ($passed > 0 and $count > 0) {
                $progress = ($passed * 100) / $count;
                $this->handleLearningProgress100Reward($progress, $user_id, $this->id);
            }
        } else if (!is_null($this->capacity)) {

            $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();
            if ($salesCount > 0) {
                $progress = ($salesCount * 100) / $this->capacity;
            }
        }
        return round($progress, 2);
    }

    public function checkShowProgress($isLearningPage = false)
    {
        $show = false;
        if (
            auth()->check() and
            $this->checkUserHasBought() and
            (
                !$this->isWebinar() or
                ($this->isWebinar() and $this->isProgressing()) or
                $isLearningPage
            )
        ) {
            $show = true;
        } else if (!is_null($this->capacity)) {
            $show = true;
        }
        return $show;
    }

    public function handleLearningProgress100Reward($progress, $userId, $itemId)
    {
        if ($progress >= 100) {
            $rewardScore = RewardAccounting::calculateScore(Reward::LEARNING_PROGRESS_100);
            RewardAccounting::makeRewardAccounting($userId, $rewardScore, Reward::LEARNING_PROGRESS_100, $itemId, true);
        }
    }

    /**
     * Function to get course progress of current student
     * @param int $user_id  User Id against which progress is required
     * @return float        Returns Progress Percentage of student rounded to 2 decimal places
     */
    // public function getProgress($user_id = -1)
    // {
    //     $progress = 0;
    //     $earnedCourseElementsTime = 0;
    //     $user = null;
    //     if ($user_id <= 0) {
    //         $user = auth()->user();
    //         $user_id = $user->id;
    //     } else {
    //         $user = User::find($user_id);
    //     }
    //     if ($this->isWebinar() and !empty($this->capacity)) {
    //         if ($this->isProgressing() and $this->checkUserHasBought($user)) {
    //             $sessions = $this->sessions;
    //             //files have no time association
    //             //$files = $this->files;
    //             if (isset($sessions) && count($sessions)) {
    //                 foreach ($sessions as $session) {
    //                     $status = CourseLearning::where('user_id', $user_id)
    //                         ->where('session_id', $session->id)
    //                         ->first();

    //                     if (!empty($status) && isset($session->duration)) {
    //                         $earnedCourseElementsTime += (int)$session->duration;
    //                     }
    //                 }
    //             }
    //         } else {
    //             $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

    //             if ($salesCount > 0) {
    //                 $progress = ($salesCount * 100) / $this->capacity;
    //             }
    //         }
    //     } elseif (
    //         !$this->isWebinar()
    //         and (auth()->check() or (isset($user_id) and $user_id > 0))
    //         and $this->checkUserHasBought($user)
    //     ) {
    //         //$user_id = auth()->id();
    //         //no time allocation with files
    //         //$files = $this->files;
    //         $textLessons = $this->textLessons;
    //         $allLessonsStudied = CourseLearning::where('user_id', $user_id)
    //         ->pluck('text_lesson_id')->toArray();

    //         foreach ($textLessons as $textLesson) {
    //             $status = in_array($textLesson->id, $allLessonsStudied);

    //             if (!empty($status) && isset($textLesson->study_time)) {
    //                 $earnedCourseElementsTime += (int)$textLesson->study_time;
    //             }
    //         }
    //     }

    //     $totalCourseElementsTime = $this->getTotalCourseElementsTime();
    //     if ($totalCourseElementsTime > 0) {
    //         $progress = ($earnedCourseElementsTime * 100) / $totalCourseElementsTime;
    //     }

    //     return round($progress, 2);
    // }

    /**
     * Function to get student's expected progress accordin to course content time and course duration
     * @param int $user_id  User Id against which expected progress is to be calculated
     * @return float        Returns Percentage Progress rounded to 2 decimal places
     */
    public function getExpectedProgress($user_id = -1)
    {
        $courseDurationInDays = $this->duration_week * 7;
        //$totalCourseElementsTime = 0;
        $user = null;
        // if the user is admin then the user_id will be -1 and no user found
        $user = User::find($user_id);
        if (! $user) {
            return 0;
        }
        $daysEnrolledOriginal = $daysEnrolled = $this->getDaysEnrolled($user_id);

        //$totalCourseElementsTime = $this->getTotalCourseElementsTime();
        //expected progress time = (total time allocated to all elements * number of enrolled days) / total days
        //expected progress percentage = (expected progress time * 100) / total course time
        //$progressTime = ($totalCourseElementsTime * $daysEnrolled) / $courseDurationInDays;
        //$progress = ($progressTime * 100) / $totalCourseElementsTime;
        //The formula can be simplified to => (days enrolled in course * 100) / total course duration in days
        $courseDurationInDays = $courseDurationInDays ?: 1;

        //Compensating User Breaks
        $enrollmentDate = $this->getEnrollmentDate($user_id);
        $beginDate = Carbon::parse($enrollmentDate);
        // $endDate = Carbon::parse($enrollmentDate)->addDays($courseDurationInDays);
        $endDate = Carbon::parse($enrollmentDate)->addDays($courseDurationInDays);
        //dd($beginDate->format('Y-m-d'), $endDate->format('Y-m-d'));
        do {
            $userBreaks = UserBreak::where(['user_id'=> $user_id,
                'status' => UserBreak::$status['approved']])
                ->whereBetween('from', [$beginDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->groupby('from')
                ->distinct()
                ->get();

            if(!empty($userBreaks) && $userBreaks->count() > 0) {
                $breakDays = 0;
                foreach($userBreaks as $break) {
                    $from = Carbon::parse($break->from);
                    $to = Carbon::parse($break->to);
                    $breakDays += $from->diffInDays($to);
                }
                //increase the begin and end date range to check again for any breaks
                //also remove the factor of including same day when calculating new date
                $beginDate->addDays($breakDays - 1);
                $endDate->addDays($breakDays - 1);
                if ($daysEnrolled > 0) {
                    $daysEnrolled -= $breakDays; //adjust daysEnrolled to discard days user was on Break
                    $daysEnrolled = ($daysEnrolled < 0) ? 0 : $daysEnrolled; //Enrolled days can never be negative
                }
            }
        } while (!empty($userBreaks) && $userBreaks->count() > 0);

        $progress = ($daysEnrolled * 100) / $courseDurationInDays;
        if ($progress > 100) {
            $progress = 100;
        }
        return round($progress, 2);
    }

    /**
     * Function to get the total time of all the elements in the current course
     * @return  integer     Total time is returned in minutes
     */
    public function getTotalCourseElementsTime()
    {
        $totalCourseElementsTime = 0;

        $sessions = $this->sessions;
        $textLesson = $this->textLessons;

        //Get days student has been enrolled in the course
        if ($this->isWebinar() and !empty($this->capacity)) {
            $sessions = $this->sessions;

            if (isset($sessions) && count($sessions)) {
                //total up any session timings
                foreach ($sessions as $session) {
                    if (isset($session->duration) && (int)$session->duration > 0) {
                        $totalCourseElementsTime += (int)$session->duration;
                    }
                }
            } else {
                $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

                if ($salesCount > 0) {
                    $progress = ($salesCount * 100) / $this->capacity;
                }
            }
        } elseif (!$this->isWebinar()) {
            //no time allocation with files
            $textLessons = $this->textLessons;
            //time isn't allotted to quizzes either

            if (isset($textLessons) && count($textLessons)) {
                foreach ($textLessons as $textLesson) {
                    if (isset($textLesson->study_time) && (int)$textLesson->study_time > 0) {
                        $totalCourseElementsTime += (int)$textLesson->study_time;
                    }
                }
            }
        }
        return $totalCourseElementsTime;
    }

    /**
     * Function to get number of days a student has been enrolled in course.
     * @param  integer $user_id Id of user to get enrolled days
     * @return integer          Return number of days student has been enrolled in course.
     */
    public function getDaysEnrolled($user_id = -1)
    {
        $daysEnrolled = 0;
        if (!isset($user_id) || $user_id < 1) {
            $user_id = auth()->id();
        }
        if (!isset($user_id) || $user_id < 1) {
            return $daysEnrolled; //not signed in, hence not enrolled
        }
        if (!empty($user_id)) {
            $enrollmentDate = $this->getEnrollmentDate($user_id);
            if (!empty($enrollmentDate) && (int)$enrollmentDate > 0) {
                    $daysEnrolled = (Carbon::parse($enrollmentDate))->diffInDays(Carbon::now());
            }
        }
        return $daysEnrolled;
    }

    /**
     * Returns enrollment date in unix time
     *
     * @param integer $user_id
     * @return mixed
     */
    public function getEnrollmentDate($user_id = -1)
    {
        if (!isset($user_id) || $user_id < 1) {
            $user_id = auth()->id();
        }
        if (!isset($user_id) || $user_id < 1) {
            return null; //for incorrect or missing user_id
        }
        if (!empty($user_id)) {
            $sale = Sale::where('buyer_id', $user_id)
                ->where('webinar_id', $this->id)
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->first();
            if (!empty($sale)) {
                if (isset($sale->created_at) && (int)$sale->created_at > 0) {
                    return (int)$sale->created_at;
                }
            }
        }
        return null;
    }

    public function getImageCover()
    {
        // return config('app_url') . $this->image_cover;
        return $this->image_cover;
    }

    public function getImage()
    {
        // return config('app_url') . $this->thumbnail;
        return $this->thumbnail;
    }

    public function getUrl()
    {
        return url('/course/' . $this->slug);
    }

    public function getLearningPageUrl()
    {
        return url('/course/learning/' . $this->slug);
    }
    public function getNoticeboardsPageUrl()
    {
        return $this->getLearningPageUrl() . '/noticeboards';
    }
    public function getForumPageUrl()
    {
        return $this->getLearningPageUrl() . '/forum';
    }

    public function isCourse()
    {
        return ($this->type == 'course');
    }

    public function isTextCourse()
    {
        return ($this->type == 'text_lesson');
    }

    public function isWebinar()
    {
        return ($this->type == 'webinar');
    }

    public function canAccess($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!empty($user)) {
            if ($user->isAdmin()) {
                return true;
            }
            return ($this->creator_id == $user->id or $this->teacher_id == $user->id);
        }

        return false;
    }

    /* public function canSale()
    {
        $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

        if ($this->type == 'webinar') {
            return ($this->start_date > time() and $salesCount < $this->capacity);
        }

        return true;
    } */
    public function canSale()
    {
        $result = true;
        if (!is_null($this->capacity)) {
            $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();
            $result = $salesCount < $this->capacity;
        }
        if ($result and $this->type == 'webinar') {
            $result = ($this->start_date > time());
        }
        return $result;
    }

    public function canJoinToWaitlist()
    {
        $hasBought = $this->checkUserHasBought();
        return ($this->enable_waitlist and !$hasBought and !$this->canSale());
    }
    public function cantSaleStatus($hasBought)
    {
        $status = '';
        if ($hasBought) {
            $status = 'js-course-has-bought-status';
        } else {
            if (!is_null($this->capacity)) {
                $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();
                if ($salesCount >= $this->capacity) {
                    $status = 'js-course-not-capacity-status';
                }
            } elseif ($this->type == 'webinar' and $this->start_date <= time()) {
                $status = 'js-course-has-started-status';
            }
        }
        return $status;
    }


    public function addToCalendarLink()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i', dateTimeFormat($this->start_date, 'Y-m-d H:i'));

        $link = Link::create($this->title, $date, $date); //->description('Cookies & cocktails!')

        return $link->google();
    }

    public function activeSpecialOffer()
    {
        $activeSpecialOffer = SpecialOffer::where('webinar_id', $this->id)
            ->where('status', SpecialOffer::$active)
            ->where('from_date', '<', time())
            ->where('to_date', '>', time())
            ->first();

        return $activeSpecialOffer ?? false;
    }

    public function nextSession()
    {
        $sessions = $this->sessions()
            ->orderBy('date', 'asc')
            ->get();
        $time = time();

        foreach ($sessions as $session) {
            if ($session->date > $time) {
                return $session;
            }
        }

        return null;
    }

    public function lastSession()
    {
        $session = $this->sessions()
            ->orderBy('date', 'desc')
            ->first();

        return $session;
    }

    public function isProgressing()
    {
        $lastSession = $this->lastSession();
        //$nextSession = $this->nextSession();
        $isProgressing = false;

        // if ($this->start_date <= time() and !empty($lastSession) and $lastSession->date > time()) {
        if ($this->start_date <= time() or (!empty($lastSession) and $lastSession->date > time())) {

            $isProgressing = true;
        }

        return $isProgressing;
    }

    /* Old method for getting share links */
    /* public function getShareLink($social)
    {
        $link = '';

        switch ($social) {
            case 'facebook':
                $link = ShareFacade::page($this->getUrl())->facebook()->getRawLinks();
                break;
            case 'twitter':
                $link = ShareFacade::page($this->getUrl())->twitter()->getRawLinks();
                break;
            case 'whatsapp':
                $link = ShareFacade::page($this->getUrl())->whatsapp()->getRawLinks();
                break;
            case 'telegram':
                $link = ShareFacade::page($this->getUrl())->telegram()->getRawLinks();
                break;
        }

        if (is_array($link) && array_key_exists($social, $link)) {
            return $link[$social];
        }
        return $link;
    } */

    public function getShareLink($social)
    {
        $link = ShareFacade::page($this->getUrl(), $this->title)
            ->facebook()
            ->twitter()
            ->whatsapp()
            ->telegram()
            ->getRawLinks();
        return !empty($link[$social]) ? $link[$social] : '';
    }

    public function isDownloadable()
    {
        $downloadable = $this->downloadable;

        if ($this->files->count() > 0) {
            $downloadableFiles = $this->files->where('downloadable', true)->count();

            if ($downloadableFiles > 0) {
                $downloadable = true;
            }
        }

        return $downloadable;
    }

    public function isOwner($userId = null)
    {
        if (empty($userId)) {
            $userId = auth()->id();
        }
        return (($this->creator_id == $userId) or ($this->teacher_id == $userId));
    }
    public function isPartnerTeacher($userId = null)
    {
        if (empty($userId)) {
            $userId = auth()->id();
        }
        $partnerTeachers = !empty($this->webinarPartnerTeacher) ? $this->webinarPartnerTeacher->pluck('teacher_id')->toArray() : [];
        return in_array($userId, $partnerTeachers);
    }
    public function getPrice()
    {
        $price = $this->price;
        $specialOffer = $this->activeSpecialOffer();
        if (!empty($specialOffer)) {
            $price = $price - ($price * $specialOffer->percent / 100);
        }
        return $price;
    }
    public function getStudentsIds()
    {
        $studentsIds = Sale::query()->where('webinar_id', $this->id)
            ->whereNull('refund_at')
            ->whereHas('buyer')
            ->pluck('buyer_id')
            ->toArray();
        // get users by installments
        $installmentOrders = InstallmentOrder::query()
            ->where('webinar_id', $this->id)
            ->where('status', 'open')
            ->whereNull('refund_at')
            ->get();
        foreach ($installmentOrders as $installmentOrder) {
            if (!empty($installmentOrder)) {
                $hasBought = true;
                if ($installmentOrder->checkOrderHasOverdue()) {
                    $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days');
                    if (empty($overdueIntervalDays) or $installmentOrder->overdueDaysPast() > $overdueIntervalDays) {
                        $hasBought = false;
                    }
                }
                if ($hasBought) {
                    $studentsIds[] = $installmentOrder->user_id;
                }
            }
        }
        // get users by gifts
        $gifts = Gift::query()
            ->where('status', 'active')
            ->where('webinar_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->get();
        foreach ($gifts as $gift) {
            $user = User::query()->select('id', 'email')->where('email', $gift->email)->first();
            if (!empty($user)) {
                $studentsIds[] = $user->id;
            }
        }
        // get users by bundle
        $bundleWebinar = BundleWebinar::where('webinar_id', $this->id)
            ->with([
                'bundle'
            ])->get();
        if ($bundleWebinar->isNotEmpty()) {
            foreach ($bundleWebinar as $item) {
                if (!empty($item->bundle)) {
                    $bundleStudents = $item->bundle->getStudentsIds();
                    $studentsIds = array_merge($studentsIds, $bundleStudents);
                }
            }
        }
        return array_unique($studentsIds);
    }
    public function sendNotificationToAllStudentsForNewQuizPublished($quiz)
    {
        $studentsIds = $this->getStudentsIds();
        $notifyOptions = [
            '[q.title]' => $quiz->title,
            '[c.title]' => $this->title
        ];
        if (count($studentsIds)) {
            foreach ($studentsIds as $studentId) {
                sendNotification("new_quiz", $notifyOptions, $studentId);
            }
        }
        $gifts = Gift::query()
            ->where('status', 'active')
            ->where('webinar_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->get();
        foreach ($gifts as $gift) {
            $user = User::query()->select('id', 'email')->where('email', $gift->email)->first();
            if (empty($user)) {
                sendNotificationToEmail("new_quiz", $notifyOptions, $gift->email);
            }
        }
    }

    public function makeCourseCertificateForUser($user)
    {
        if (!empty($user) and $this->certificate and $this->getProgress(true) >= 100) {
            $check = Certificate::where('type', 'course')
                ->where('student_id', $user->id)
                ->where('webinar_id', $this->id)
                ->first();

            if (empty($check)) {
                $makeCertificate = new MakeCertificate();
                $userCertificate = $makeCertificate->saveCourseCertificate($user, $this);

                $certificateReward = RewardAccounting::calculateScore(Reward::CERTIFICATE);
                RewardAccounting::makeRewardAccounting($userCertificate->student_id, $certificateReward, Reward::CERTIFICATE, $userCertificate->id, true);
            }
        }
    }
}
