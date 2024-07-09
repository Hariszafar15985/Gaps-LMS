<?php

namespace App\Models;

use App\Models\Traits\SequenceContent;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class WebinarChapter extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    protected $table = 'webinar_chapters';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $chapterFile = 'file';
    static $chapterSession = 'session';
    static $chapterTextLesson = 'text_lesson';

    static $chapterActive = 'active';
    static $chapterInactive = 'inactive';

    static $chapterTypes = ['file', 'session', 'text_lesson'];

    static $chapterStatus = ['active', 'inactive'];

    public $translatedAttributes = ['title'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    public function sessions()
    {
        return $this->hasMany('App\Models\Session', 'chapter_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\File', 'chapter_id', 'id');
    }

    public function textLessons()
    {
        return $this->hasMany('App\Models\TextLesson', 'chapter_id', 'id');
    }

    public function assignments()
    {
        return $this->hasMany('App\Models\WebinarAssignment', 'chapter_id', 'id');
    }

    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'chapter_id', 'id');
    }

    public function chapterItems()
    {
        return $this->hasMany('App\Models\WebinarChapterItem', 'chapter_id', 'id')->orderBy('order','asc')->orderBy('id','asc');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function getDuration()
    {
        $type = $this->type;
        $time = 0;

        /* switch ($type) {
            case self::$chapterFile:
                $time = 0;
                break;
            case self::$chapterSession:
                $time = $this->sessions->sum('duration');
                break;
            case self::$chapterTextLesson:
                $time = $this->textLessons->sum('study_time');
                break;
        } */


        $time += $this->sessions->sum('duration');
        $time += $this->textLessons->sum('study_time');

        return $time;
    }


    /* public function getTopicsCount()
    {
        $type = $this->type;
        $count = 0;

        switch ($type) {
            case self::$chapterFile:
                $count = $this->files->count();
                break;
            case self::$chapterSession:
                $count = $this->sessions->count();
                break;
            case self::$chapterTextLesson:
                $count = $this->textLessons->count();
                break;
        }

        return $count;
    } */

    public function getTopicsCount($withQuiz = false)
    {
        $count = 0;
        $count += $this->files->where('status', 'active')->count();
        $count += $this->sessions->where('status', 'active')->count();
        $count += $this->textLessons->where('status', 'active')->count();
        $count += $this->assignments->where('status', 'active')->count();
        if ($withQuiz) {
            $count += $this->quizzes->where('status', 'active')->count();
        }
        return $count;
    }
}
