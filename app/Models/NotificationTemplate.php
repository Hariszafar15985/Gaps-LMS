<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'notification_templates';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $templateKeys = [
        'email' => '[u.email]',
        'course_enroll_date' => '[c.enrol.date]',
        'mobile' => '[u.mobile]',
        'real_name' => '[u.name]',
        'instructor_name' => '[instructor.name]',
        'student_name' => '[student.name]',
        'student_id' => '[student.id]',
        'group_title' => '[u.g.title]',
        'badge_title' => '[u.b.title]',
        'course_title' => '[c.title]',
        'quiz_title' => '[q.title]',
        'quiz_result' => '[q.result]',
        'support_ticket_title' => '[s.t.title]',
        'contact_us_title' => '[c.u.title]',
        'time_and_date' => '[time.date]',
        'link' => '[link]',
        'rate_count' => '[rate.count]',
        'amount' => '[amount]',
        'payout_account' => '[payout.account]',
        'financial_doc_desc' => '[f.d.description]',
        'financial_doc_type' => '[f.d.type]',
        'subscribe_plan_name' => '[s.p.name]',
        'promotion_plan_name' => '[p.p.name]',
        'consultant_name' => '[consultant.name]',
        'placement_name' => '[placement.company_name]',
        'placement_abn' => '[placement.abn]',
        'placement_employer_address' => '[placement.employer_address]',
        'placement_contact_person' => '[placement.contact_person]',
        'placement_phone' => '[placement.phone]',
        'placement_employment_type' => '[placement.employment_type]',
        'placement_pay_rate' =>'[placement.pay_rate]',
        'placement_hours_per_week' =>'[placement.hours_per_week]',
    ];

    static $notificationTemplateAssignSetting = [
        'admin' => ['course_enroll_date','new_comment_admin', 'support_message_admin', 'support_message_replied_admin', 'promotion_plan_admin', 'new_contact_message','payout_request_admin','confirm_enrollment_notify', 'student_unit_submission', "welcome_to_gaps_education" , "study_inactive_notification"],
        'user' => ['new_badge', 'change_user_group', 'new_student_created', 'complete_student_signup_request', 'student_enrolment_completed'],
        'course' => ['course_created', 'course_approve', 'course_reject', 'new_comment', 'support_message', 'support_message_replied', 'new_rating', 'webinar_reminder'],
        'financial' => ['new_financial_document', 'payout_request', 'payout_proceed', 'offline_payment_request', 'offline_payment_approved', 'offline_payment_rejected'],
        'sale_purchase' => ['new_sales', 'new_purchase'],
        'plans' => ['new_subscribe_plan', 'promotion_plan'],
        'appointment' => ['new_appointment', 'new_appointment_link', 'appointment_reminder', 'meeting_finished'],
        'quiz' => ['new_certificate', 'waiting_quiz', 'waiting_quiz_result',
                    //including new quiz notificaiton templates
                    'quiz_result_passed',
                    'quiz_result_failed']
    ];
}
