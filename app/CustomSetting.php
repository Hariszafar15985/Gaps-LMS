<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomSetting extends Model
{
    protected $fillable = [
        "key",
        "value",
    ];

   public $showDripFeedInSideBar = 'drip_feed_on_course_side_bar';
}
