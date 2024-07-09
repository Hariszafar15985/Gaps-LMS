<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('organ_id')->unsigned()->nullable();
            $table->timestamp('check_in_time')->default(DB::Raw('CURRENT_TIMESTAMP'));
            $table->bigInteger('ip')->nullable();
            $table->tinyInteger('is_manual')->nullable();
            $table->integer('manual_added_by')->unsigned()->nullable();
            $table->timestamp('manual_added_at')->nullable();
            $table->timestamps();

            $table->foreign('organ_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreign('manual_added_by')->on('users')->references('id');
            $table->index('ip');
            $table->index('check_in_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}
