<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewStatusToQuizzesResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes_results', function (Blueprint $table) {
            // adding new status 'attempting' to this table
            $prefix = DB::getTablePrefix(); // Get the table prefix for the current database connection

            // Modify the 'status' column using a raw SQL query with a table prefix
            DB::statement("ALTER TABLE " . $prefix . "quizzes_results MODIFY COLUMN status ENUM('passed', 'failed', 'waiting', 'attempting') DEFAULT 'attempting'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes_results', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
