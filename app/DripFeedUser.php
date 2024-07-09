<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DripFeedUser extends Model
{
    protected $fillable = [
        "user_id",
        "text_lesson_id",
        "webinar_id",
    ];
}
