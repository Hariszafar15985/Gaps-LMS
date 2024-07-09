<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseVisibilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_visibilities', function (Blueprint $table) {
            $table->id();

            $table->index('course_id');
            $table->foreign('course_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->integer("course_id")->unsigned()->nullable();

            $table->index('organization_id');
            $table->foreign('organization_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer("organization_id")->unsigned()->nullable();

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
        Schema::dropIfExists('course_visibilities');
    }
}
