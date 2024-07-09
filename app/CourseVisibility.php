<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseVisibility extends Model
{
    protected $fillable = [
        "course_id",
        "organization_id",
        "visible_to_all",
    ];
}
