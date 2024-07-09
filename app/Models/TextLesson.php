<?php

namespace App\Models;

use App\Models\Traits\SequenceContent;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class TextLesson extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    protected $table = 'text_lessons';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $Active = 'active';
    static $Inactive = 'inactive';
    static $Status = ['active', 'inactive'];

    protected $fillable = [
        "drip_feed",
        "creator_id",
        'webinar_id',
        'chapter_id',
        'image',
        'study_time',
        'accessibility',
        'order',
        'status',
        'show_after_days',
        'created_at',
    ];

    public $translatedAttributes = ['title', 'summary', 'content'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getSummaryAttribute()
    {
        return getTranslateAttributeValue($this, 'summary');
    }

    public function getContentAttribute()
    {
        return getTranslateAttributeValue($this, 'content');
    }


    public function attachments()
    {
        return $this->hasMany('App\Models\TextLessonAttachment', 'text_lesson_id', 'id');
    }

    public function learningStatus()
    {
        return $this->hasOne('App\Models\CourseLearning', 'text_lesson_id', 'id');
    }

    public function audioFile()
    {
        return $this->hasOne('App\Models\AudioAttachment', 'text_lesson_id', 'id');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
    }

    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'text_lesson_id', 'id');
    }

    public function checkPassedItem()
    {
        $result = false;

        if (auth()->check()) {
            $check = $this->learningStatus()->where('user_id', auth()->id())->count();

            $result = ($check > 0);
        }

        return $result;
    }
}
