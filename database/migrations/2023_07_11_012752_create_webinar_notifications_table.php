<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_notifications', function (Blueprint $table) {
            $table->id();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer("user_id")->unsigned();

            $table->index('webinar_id');
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->integer("webinar_id")->unsigned();

            $table->boolean("is_completed")->default(0);

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
        Schema::dropIfExists('webinar_notifications');
    }
}
