<?php

namespace App\Models;

use App\Models\Traits\SequenceContent;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class File extends Model implements TranslatableContract
{
    use Translatable;
    use SequenceContent;

    public $timestamps = false;
    protected $table = 'files';
    protected $guarded = ['id'];

    static $accessibility = [
        'free', 'paid'
    ];

    static $videoTypes = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'avchd', 'flv', 'f4v', 'swf', 'mpeg-2', 'webm', 'video'];
    static $fileTypes = [
        'pdf', 'power point', 'sound', 'video', 'image', 'archive', 'document', 'project'
    ];

    static $Active = 'active';
    static $Inactive = 'inactive';
    static $fileStatus = ['active', 'inactive'];

    public $translatedAttributes = ['title', 'description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }


    public function chapter()
    {
        return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
    }

    public function learningStatus()
    {
        return $this->hasOne('App\Models\CourseLearning', 'file_id', 'id');
    }

    public function isVideo()
    {
        return (in_array($this->file_type, self::$videoTypes));
    }

    public function getFileDuration()
    {
        $duration = 0;

        if ($this->storage == 'local') {
            $file_path = public_path($this->file);

            $getID3 = new \getID3;
            $file = $getID3->analyze($file_path);

            if (!empty($file) and !empty($file['playtime_seconds'])) {
                $duration = $file['playtime_seconds'];
            }
        }

        return convertMinutesToHourAndMinute($duration);
    }

    public function getFileStorageService()
    {
        $storageService = 'local';

        if ($this->storage != 'upload') {
            $storageService = 'youtube';

            if (strpos($this->file, 'vimeo')) {
                $storageService = 'vimeo';
            }
        }

        return $storageService;
    }

    public function getIconByType($type = null)
    {
        $icon = 'file';

        if (empty($type)) {
            $type = $this->file_type;
        }

        if (!empty($type)) {
            if (in_array($type, ['pdf', 'powerpoint', 'document'])) {
                $icon = 'file-text';
            } else if (in_array($type, ['sound'])) {
                $icon = 'volume-2';
            } else if (in_array($type, ['video'])) {
                $icon = 'film';
            } else if (in_array($type, ['image'])) {
                $icon = 'image';
            } else if (in_array($type, ['archive'])) {
                $icon = 'archive';
            }
        }

        return $icon;
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

    public function getVolume()
    {
        return $this->volume . ' MB';
    }
}
