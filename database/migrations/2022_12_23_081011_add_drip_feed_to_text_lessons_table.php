<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDripFeedToTextLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('text_lessons', function (Blueprint $table) {
            $table->text('drip_feed')->after('order')->nullable();
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
            $table->dropColumn('drip_feed');
            // $table->dropColumn('drip_feed_date');
        });
    }
}
