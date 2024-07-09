<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingSidebar extends Model
{
    protected $table = 'settings_sidebar';

    public static function getSidebarSettings() {
        return $settings = SettingSidebar::orderby('id', 'desc')
            ->first();
    }
}
