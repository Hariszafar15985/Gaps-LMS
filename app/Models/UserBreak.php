<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBreak extends Model
{
    public static $breakTypes = [
        'casual'    =>  'Casual',
        'marriage'  =>  'Marriage',
        'other'     =>  'Other',
        'sick'      =>  'Sick'
    ];

    public static $status = [
        'approved'  =>  'approved', 
        'canceled'  =>  'canceled', 
        'pending'   =>  'pending', 
        'rejected'  =>  'rejected'
    ];
    
    //
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->withTrashed();
    }

    public function requestedBy()
    {
        return $this->belongsTo('App\User', 'requested_by', 'id')->withTrashed();
    }

}
