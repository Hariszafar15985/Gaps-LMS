<?php

namespace App\Models;

use DateTimeImmutable;
use Spatie\CalendarLinks\Link;
use Illuminate\Database\Eloquent\Model;

class ReserveMeeting extends Model
{
    protected $table = "reserve_meetings";
    public static $open = "open";
    public static $finished = "finished";
    public static $pending = "pending";
    public static $canceled = "canceled";

    public $timestamps = false;

    protected $guarded = ['id'];

    public function meetingTime()
    {
        return $this->belongsTo('App\Models\MeetingTime', 'meeting_time_id', 'id');
    }

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting', 'meeting_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->withTrashed();
    }


    //following function was throwing error on the \DateTime::create()
    // Follwoing commented method will be removed from the model later
    // public function addToCalendarLink()
    // {
    //     $day = $this->day;
    //     $times = $this->meetingTime->time;
    //     $times = explode('-', $times);
    //     $start_time = date("H:i", strtotime($times[0]));
    //     $end_time = date("H:i", strtotime($times[1]));

    //     $startDate = \DateTime::createFromFormat('Y-M-d H:i', $day . ' ' . $start_time);
    //     $endDate = \DateTime::createFromFormat('Y-M-d H:i', $day . ' ' . $end_time);
    //     // dd($startDate, $endDate);

    //     $link = Link::create('Meeting', $startDate, $endDate); //->description('Cookies & cocktails!')

    //     return $link->google();
    // }

    //In the following method \DateTime is replaced with the DateTimeImmutable trait
    public function addToCalendarLink()
    {
        $day = $this->day;
        $times = $this->meetingTime->time;
        $times = explode('-', $times);
        $start_time = date("H:i", strtotime($times[0]));
        $end_time = date("H:i", strtotime($times[1]));

        // Use DateTimeImmutable for better immutability
        $startDate = new DateTimeImmutable($day . ' ' . $start_time);
        $endDate = new DateTimeImmutable($day . ' ' . $end_time);

        $link = Link::create('Meeting', $startDate, $endDate);// ->description('Meeting Description'); // Add description if needed

        return $link->google();
    }
}
