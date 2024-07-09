<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profile_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('title', 250);
            $table->string('type', 50);
            $table->text('message');
            $table->integer('creator_id')->unsigned()->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('creator_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->foreign('creator_id')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile_notes');
    }
}
