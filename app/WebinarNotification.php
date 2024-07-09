<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebinarNotification extends Model
{
    protected $fillable = [
        "user_id",
        "webinar_id",
        "is_completed",
    ];
}
