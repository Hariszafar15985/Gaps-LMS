<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudioAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio_attachments', function (Blueprint $table) {
            $table->id();

            $table->index('attached_by');
            $table->foreign('attached_by')->references('id')->on('users')->onDelete('cascade');
            $table->integer("attached_by")->unsigned();

            $table->index('webinar_id');
            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->integer("webinar_id")->unsigned();

            $table->index('text_lesson_id');
            $table->foreign('text_lesson_id')->references('id')->on('text_lessons')->onDelete('cascade');
            $table->integer("text_lesson_id")->unsigned()->nullable();

            $table->text("file_name");

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
        Schema::dropIfExists('audio_attachments');
    }
}
