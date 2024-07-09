<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddNewSettingsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Removing Foreign Key Constraints
        Schema::disableForeignKeyConstraints();

        //Ideally this should have been handled solely using Seeders, but seeds are being ignored by devs
        Artisan::call('db:seed', [
            '--class' => 'UpdateSettingsSeeder',
            '--force' => true
        ]);

        //Adding Foreign Key Constraints
        Schema::enableForeignKeyConstraints();

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
}
