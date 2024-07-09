<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class InsertDeataInWebinarChapterItemsByCallingSeeder extends Migration
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

        Artisan::call('db:seed', [
            '--class' => WebinarChapterItemSeeder::class,
            '--force' => 'true'
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

    }
}
