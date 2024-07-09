<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToCourseLearning extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_learning', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('course_learning', 'text_lesson_status'))
            {
                $table->tinyInteger('text_lesson_status')->after('session_id')->default(0);
                $table->index('text_lesson_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_learning', function (Blueprint $table) {
            //
            if (Schema::hasColumn('course_learning', 'text_lesson_status'))
            {
                $table->dropIndex('course_learning_text_lesson_status_index');
                $table->dropColumn('text_lesson_status');
            }
        });
    }
}
