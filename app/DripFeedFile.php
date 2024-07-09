<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DripFeedFile extends Model
{
    protected $fillable = [
        "user_id",
        "webinar_id",
        "file_id",
    ];
}
