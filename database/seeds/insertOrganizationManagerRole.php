<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class insertOrganizationManagerRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $roleExists  = DB::table('roles')->where('name', 'organization_manager')->count();
        if(!$roleExists) {
            DB::table('roles')
            ->insert([
                'id' => 5,
                'name' => 'organization_manager',
                'caption' => 'Organization Manager',
                'users_count' => 0,
                'is_admin' => 0,
                'created_at' => strtotime(now())
                ]);
        }
    }
}
