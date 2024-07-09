<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextLessonIdColumnToQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            //
            $table->integer('text_lesson_id')->unsigned()->nullable()->after('chapter_id');

            $table->foreign('text_lesson_id')->on('text_lessons')->references('id');
            $table->index('text_lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            //
            $table->dropColumn('text_lesson_id');
        });
    }
}
