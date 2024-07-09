<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioAttachment extends Model
{
    protected $fillable = [
        "attached_by",
        "webinar_id",
        "text_lesson_id",
        "file_name",
    ];
}
