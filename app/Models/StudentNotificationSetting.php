<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotificationSetting extends Model
{
    protected $table   = 'student_notifications_settings';
    protected $guarded = ['id'];

    public static $default = ['daily','weekly','fortnightly','monthly'];

}
