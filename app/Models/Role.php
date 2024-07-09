<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;
    static $admin = 'admin';
    static $user = 'user';
    static $teacher = 'teacher';
    static $organization = 'organization';
    static $organization_manager = 'organization_manager'; //higher manager
    static $organization_sub_manager = 'organization_sub_manager'; //manager
    static $organization_staff = 'organization_staff'; //consultant

    protected $guarded = ['id'];

    public function canDelete()
    {
        switch ($this->name) {
            case self::$admin:
            case self::$user:
            case self::$organization:
            case self::$organization_manager:
            case self::$organization_staff:
            case self::$teacher:
                return false;
                break;
            default:
                return true;
        }
    }

    public function users()
    {
        return $this->hasMany('App\User', 'role_id', 'id');
    }

    public function isDefaultRole()
    {
        return in_array($this->name, [self::$admin, self::$user, self::$organization, self::$organization_manager, self::$teacher]);
    }

    public static function getUserRoleId()
    {
        $id = 1; // user role id

        $role = self::where('name', self::$user)->first();

        return !empty($role) ? $role->id : $id;
    }

    public static function getTeacherRoleId()
    {
        $id = 4; // teacher role id

        $role = self::where('name', self::$teacher)->first();

        return !empty($role) ? $role->id : $id;
    }

    public static function getOrganizationRoleId()
    {
        $id = 3; // organization role id

        $role = self::where('name', self::$organization)->first();

        return !empty($role) ? $role->id : $id;
    }

    
    public static function getOrganizationManagerRoleId()
    {
        $id = 5; // organization manager (aka higher manager) role id

        $role = self::where('name', self::$organization_manager)->first();

        return !empty($role) ? $role->id : $id;
    }
    
    public static function getOrganizationSubManagerRoleId()
    {
        $id = 7; // organization sub manager role id

        $role = self::where('name', self::$organization_sub_manager)->first();

        return !empty($role) ? $role->id : $id;
    }
    
    public static function getOrganizationStaffRoleId()
    {
        $id = 8; // organization staff (aka consultant) role id

        $role = self::where('name', self::$organization_staff)->first();

        return !empty($role) ? $role->id : $id;
    }


}
