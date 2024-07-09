<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDripFeedAfterDaysToTextLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('text_lessons', function (Blueprint $table) {
            $table->text('show_after_days')->after("drip_feed")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('text_lessons', function (Blueprint $table) {
            $table->dropColumn('show_after_days');
        });
    }
}
