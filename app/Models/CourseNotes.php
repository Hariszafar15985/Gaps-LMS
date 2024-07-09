<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseNotes extends Model
{
    protected $fillable = [
        "user_id",
        "webinar_id",
        "lesson_id",
        "type",
        "note_text",
        "file_id"
    ];
}
