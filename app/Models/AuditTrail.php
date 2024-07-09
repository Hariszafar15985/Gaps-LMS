<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    //
    public $timestamps = false;
    protected $guarded = ['id', 'created_at'];

    //Types of activities that are audited. This reference list shall be updated with time as needed
    const auditType = [
        'attendance' => 'attendance',
        'course_enrolment' => 'course_enrolment',
        'general' => 'general',
        'login' => 'login',
        'logout' => 'logout',
        'quiz_created' => 'quiz_created',
        'quiz_edited' => 'quiz_edited',
        'quiz_checked' => 'quiz_checked',
        'quiz_started' => 'quiz_started',
        'quiz_submitted' => 'quiz_submitted',
        'quiz_reviewed' => 'quiz_reviewed',
        'quiz_reattempted' => 'quiz_reattempted',
        'notification_sent' => 'notification_sent',
        'paid_course_purchase' => 'paid_course_purchase',
        'profile_note_added' => 'profile_note_added',
        'profile_note_edited' => 'profile_note_edited',
        'user_break_status' => 'user_break_status',
        'user_break_requested' => 'user_break_requested',
        'enrolment_completed' => 'enrolment_completed',
        'course_duplicated' => 'course_duplicated',
        'chapter_duplicated' => 'chapter_duplicated',
        'text_lesson_duplicated' => 'text_lesson_duplicated',
        'enrolment_completion_notification' => 'enrolment_completion_notification',
    ];
}
