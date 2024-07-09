<?php

namespace App\Traits;

use App\Mail\SendNotifications;
use App\Models\AuditTrail;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Webinar;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait UserCourseRegistrationTrait
{
    /**
     * Method to enrol student into a course
     *
     * @param string $slug  The slug of the course for enrolment
     * @param string $payment_type  Whether the course is 'paid' or 'free'
     * @param integer $id   ID of the user being enroled in the course
     * @param boolean $enrolAtCreation  is enrolment being performed at the time of user creation
     * @return void
     */
    public function processEnrolment(string $slug, string $payment_type, int $id, $enrolAtCreation = false)
    {
        $authUser = auth()->user();

        $user = User::where('id', $id)->first();
        // echo "<pre>";
        // print_r($user->id); exit;

        if (
            empty($user) || $user->role_name !== Role::$user
            || empty($user->organ_id)
        ) {
            $toastData = [
                'title' => trans('panel.invalid_user'),
                'msg' => trans('panel.invalid_or_missing_user_reference'),
                'status' => 'error'
            ];
            if ($enrolAtCreation) {
                return false;
            }
            return back()->with(['toast' => $toastData]);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->join("webinar_translations", "webinar_translations.webinar_id", "webinars.id")
            ->select("webinars.*", "webinar_translations.title")
            ->first();
        //     echo "<pre>";
        // print_r($course);exit;
        if (!in_array($payment_type, ['free', 'paid'])) {
            $toastData = [
                'title' => trans('panel.invalid_request'),
                'msg' => trans('panel.invalid_course_payment_type'),
                'status' => 'error'
            ];
            if ($enrolAtCreation) {
                return false;
            }
            return back()->with(['toast' => $toastData]);
        }
        if (!empty($course)) {
            if (strtolower($payment_type) === 'free') {

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    if ($enrolAtCreation) {
                        return false;
                    }
                    return back()->with(['toast' => $toastData]);
                }

                $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
                if (isset($alreadyPurchased->id)) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => 'Course already purchased',
                        'status' => 'error'
                    ];
                    if ($enrolAtCreation) {
                        //although this will never be true at the time of creation
                        return false;
                    }
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $id,
                    'seller_id' => $course->creator_id,
                    'webinar_id' => $course->id,
                    'type' => Sale::$webinar,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
                ]);

                $toastData = [
                    'title' => '',
                    'msg' => trans('cart.success_pay_msg_for_free_course'),
                    'status' => 'success'
                ];

                if (env('APP_ENV') == 'production') {
                    $title = $course->slug;
                    $message = trans('cart.success_pay_msg_for_free_course');
                    Mail::to($user->email)
                        ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                    Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
                    // Send notification to user for enrollment confirmation
                    $template = 'confirm_enrollment_notify';
                    // echo "<pre>";
                    // print_r($user->full_name); exit;
                    $notifyOptions = [
                        '[c.title]' => $course->title,
                        "[student.name]" => $user->full_name,
                        "[u.email]" => $user->email,
                        "[u.name]" => $user->full_name,
                        "[c.enrol.date]" => Carbon::today()->format("D d F, Y"),
                        "[time.date]" => $user->schedule
                    ];
                    // echo $user->id;exit;
                    $details = [
                        "name" => $user->full_name,
                        "email" => $user->email,
                        "courseTitle" => $course->title,
                        "name" => $user->full_name,
                        "enrollDate" => Carbon::today()->format("D d F, Y"),
                        "scheduleDate" => $user->schedule
                    ];

                    Mail::to($user->email)->send(new \App\Mail\AssessmentCompleteMail($details));
                    sendNotification($template, $notifyOptions, $user->id);
                    sendNotification('confirm_enrollment_notify', $notifyOptions, $user->id);

                }

                if ($enrolAtCreation) {
                    return true;
                }



                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => csrf_token(),
                    'created_at' => Carbon::now()
                ]);


                return back()->with(['toast' => $toastData]);
            } else {
                if (!empty($course->price) and $course->price > 0) {

                    $organizationId = $user->organ_id;

                    $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
                    if (isset($alreadyPurchased->id)) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => 'Course already purchased',
                            'status' => 'error'
                        ];
                        if ($enrolAtCreation) {
                            return false;
                        }
                        return back()->with(['toast' => $toastData]);
                    }

                    //1. Enter sale against organization
                    //create sale against organization
                    $price = $course->price;
                    $financialSettings = getFinancialSettings();
                    $taxPrice = 0;
                    if (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0 and $price > 0) {
                        $tax = $financialSettings['tax'];
                        $taxPrice = $price * $tax / 100;
                    }
                    $totalAmount = $price + $taxPrice;

                    $saleTime = time();
                    $organizationSale = Sale::create([
                        'buyer_id' => $organizationId,
                        'seller_id' => $course->creator_id,
                        'webinar_id' => $course->id,
                        'type' => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount' => $price,
                        'tax' => $taxPrice,
                        'total_amount' => $totalAmount,
                        'payment_status' => Sale::$paymentStatus['pending'],
                        'created_at' => $saleTime,
                        'paid_for' => $id,
                    ]);


                    //2. Enter sale against student
                    if (!empty($organizationSale->id)) {
                        //$auditedUser = User::find($break->user_id);
                        $ip = null;
                        $ip = getClientIp();
                        $ipLong = ip2long($ip);

                        $organizationUser = User::where('id', $organizationId)->first();
                        $auditMessage = "User {$authUser->full_name} ({$authUser->id}) enrolled student ({$user->full_name} [{$user->id}]) in course {$course->title} ({$course->id}) on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
                        $audit = new AuditTrail();
                        $audit->user_id = $authUser->id;
                        $audit->organ_id = $organizationId;
                        $audit->role_name = $authUser->role_name;
                        $audit->audit_type = AuditTrail::auditType['paid_course_purchase'];
                        $audit->added_by = null;
                        $audit->description = $auditMessage;
                        $audit->ip = $ipLong;
                        $audit->save();

                        $studentSale = Sale::create([
                            'buyer_id' => $id,
                            'seller_id' => $course->creator_id,
                            'webinar_id' => $course->id,
                            'type' => Sale::$webinar,
                            'payment_method' => Sale::$credit,
                            'amount' => $price,
                            'tax' => $taxPrice,
                            'discount' =>   $totalAmount,
                            'total_amount' => 0,
                            'created_at' => $saleTime,
                            'sale_reference_id' => $organizationSale->id,
                            'self_payed' => 0,
                        ]);

                        if (!empty($studentSale->id)) {

                            $auditMessage = "User {$user->full_name} ({$user->id}) enrolled in course {$course->title} ({$course->id}) by {$authUser->full_name} ({$authUser->id}), on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
                            $audit = new AuditTrail();
                            $audit->user_id = $user->id;
                            $audit->organ_id = $organizationId;
                            $audit->role_name = $user->role_name;
                            $audit->audit_type = AuditTrail::auditType['course_enrolment'];
                            $audit->added_by = $authUser->id;
                            $audit->description = $auditMessage;
                            $audit->ip = $ipLong;
                            $audit->save();

                            $toastData = [
                                'title' => '',
                                'msg' => trans('cart.success_pay_msg_for_course'),
                                'status' => 'success'
                            ];

                            if (env('APP_ENV') == 'production') {
                                $title = $course->slug;
                                $message = trans('cart.success_pay_msg_for_course');
                                Mail::to($user->email)
                                    ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                                Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
                                // Send notification to user for enrollment confirmation
                                $template = 'confirm_enrollment_notify';
                                // echo "<pre>";
                                // print_r($user->full_name); exit;
                                $notifyOptions = [
                                    '[c.title]' => $course->title,
                                    "[student.name]" => $user->full_name,
                                    "[u.email]" => $user->email,
                                    "[u.name]" => $user->full_name,
                                    "[c.enrol.date]" => Carbon::today()->format("D d F, Y"),
                                    "[time.date]" => $user->schedule
                                ];
                                $details = [
                                    "name" => $user->full_name,
                                    "email" => $user->email,
                                    "courseTitle" => $course->title,
                                    "name" => $user->full_name,
                                    "enrollDate" => Carbon::today()->format("D d F, Y"),
                                    "scheduleDate" => $user->schedule
                                ];

                                Mail::to($user->email)->send(new \App\Mail\AssessmentCompleteMail($details));
                                sendNotification($template, $notifyOptions, $user->id);
                            }

                            DB::table('password_resets')->insert([
                                'email' => $user->email,
                                'token' => csrf_token(),
                                'created_at' => Carbon::now()
                            ]);

                            if ($enrolAtCreation) {
                                return true;
                            }
                            return back()->with(['toast' => $toastData]);
                        }
                    }
                }

                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.course_not_paid'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        }
        abort(404);
    }
}
