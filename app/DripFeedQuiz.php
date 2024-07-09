<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DripFeedQuiz extends Model
{
    protected $fillable = [
        "user_id",
        "webinar_id",
        "quiz_id",
    ];
}
