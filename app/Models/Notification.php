<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $AdminSender = 'admin';
    static $SystemSender = 'system';

    static $notificationsType = ['single', 'all_users', 'students', 'instructors', 'organizations', 'group'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->withTrashed();
    }

    public function senderUser()
    {
        return $this->belongsTo('App\User', 'sender_id', 'id')->withTrashed();
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id', 'id');
    }

    public function notificationStatus()
    {
        return $this->hasOne('App\Models\NotificationStatus', 'notification_id', 'id');
    }
}
