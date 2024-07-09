<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDripFeedColumnDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('text_lessons', function (Blueprint $table) {
            $table->boolean('drip_feed')->change();
            $table->smallInteger('show_after_days')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('text_lessons', function (Blueprint $table) {
            //previous data type was text at the time of creating this migration for both the columns
            $table->text('drip_feed')->change();
            $table->text('show_after_days')->change();
        });
    }
}
