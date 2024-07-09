<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTypeToQuizzesQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes_questions', function (Blueprint $table) {
            // $prefix = DB::getTablePrefix(); // Get the table prefix for the current database connection
            // Modify the 'status' column using a raw SQL query with a table prefix
            DB::statement("ALTER TABLE `quizzes_questions` CHANGE `type` `type` ENUM('multiple','descriptive','fillInBlank','matchingListText','matchingListImage','fileUpload','information_text') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes_questions', function (Blueprint $table) {
            //
        });
    }
}
