<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class OrganizationSite extends Model
{
    //
    protected $guarded = ['id'];

    public function organization()
    {
        return $this->belongsTo(User::class,'organ_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_site_user', 'site_id', 'user_id');
    }
}
