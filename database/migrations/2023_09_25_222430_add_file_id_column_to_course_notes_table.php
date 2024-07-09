<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//This migration is added up to keep record of course notes if the course item is file not a text lesson
class AddFileIdColumnToCourseNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('file_id')->nullable()->after('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_notes', function (Blueprint $table) {
            $table->dropColumn('file_id');
        });
    }
}
