<?php

namespace App\Models;

use App\Traits\QuizTrait;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Quiz extends Model implements TranslatableContract
{
    use Translatable;
    use QuizTrait;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public $timestamps = false;
    protected $table = 'quizzes';
    protected $guarded = ['id'];

    protected $fillable = [
        "drip_feed",
        "show_after_days",
        'webinar_id' ,
        'chapter_id' ,
        'text_lesson_id' ,
        'creator_id' ,
        'webinar_title' ,
        'attempt' ,
        'pass_mark' ,
        'time' ,
        'status' ,
        'certificate' ,
        'created_at' ,
    ];

    public $translatedAttributes = ['title'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    public function quizQuestions()
    {
        return $this->hasMany('App\Models\QuizzesQuestion', 'quiz_id', 'id')->orderBy("order", "asc");
    }

    public function quizResults()
    {
        return $this->hasMany('App\Models\QuizzesResult', 'quiz_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id')->withTrashed();
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id')->withTrashed();
    }

    public function certificates()
    {
        return $this->hasMany('App\Models\Certificate', 'quiz_id', 'id');
    }


    public function increaseTotalMark($grade)
    {
        $total_mark = $this->total_mark + $grade;
        return $this->update(['total_mark' => $total_mark]);
    }

    public function decreaseTotalMark($grade)
    {
        $total_mark = $this->total_mark - $grade;
        return $this->update(['total_mark' => $total_mark]);
    }

    public function getUserCertificate($user, $quiz_result)
    {
        if (!empty($user) and !empty($quiz_result)) {
            return Certificate::where('quiz_id', $this->id)
                ->where('student_id', $user->id)
                ->where('quiz_result_id', $quiz_result->id)
                ->first();
        }

        return null;
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
    }

    public function textLesson()
    {
        return $this->belongsTo('App\Models\TextLesson', 'text_lesson_id', 'id');
    }
}
