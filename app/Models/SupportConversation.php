<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    protected $table = 'support_conversations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_id', 'id')->withTrashed();
    }

    public function supporter()
    {
        return $this->belongsTo('App\User', 'supporter_id', 'id')->withTrashed();
    }

}
