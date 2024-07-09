<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_notifications_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('organ_id')->unsigned()->nullable();
            $table->text('type')->comment('can be in json format');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->index('user_id');
            $table->index('organ_id');
            $table->index(['user_id','organ_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_notifications_settings');
    }
}
