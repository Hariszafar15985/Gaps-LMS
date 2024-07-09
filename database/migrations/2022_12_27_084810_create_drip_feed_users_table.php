<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDripFeedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drip_feed_users', function (Blueprint $table) {
            $table->id();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer("user_id")->unsigned();

            $table->index('text_lesson_id');
            $table->foreign('text_lesson_id')->references('id')->on('text_lessons')->onDelete('cascade');
            $table->integer("text_lesson_id")->unsigned();

            $table->index('webinar_id');
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->integer("webinar_id")->unsigned();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drip_feed_users');
    }
}
