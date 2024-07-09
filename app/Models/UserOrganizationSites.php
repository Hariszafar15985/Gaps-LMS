<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserOrganizationSites extends Model
{
    //
    protected $guarded = ['id'];
    public $timestamps = false;
    public function organization()
    {
        return $this->belongsTo(User::class, 'organ_id', 'id')->where('role_name', Role::$organization);
    }

    public function organizationSite()
    {
        return $this->belongsTo(OrganizationSite::class, 'organization_site_id', 'id');
    }

}
