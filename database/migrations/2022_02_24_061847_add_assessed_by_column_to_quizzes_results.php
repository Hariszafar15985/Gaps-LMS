<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssessedByColumnToQuizzesResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes_results', function (Blueprint $table) {
            //
            $table->integer('assessed_by')->after('results')->nullable()->default(null);
            $table->index('assessed_by');
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
            //
            $table->dropIndex('quizzes_results_assessed_by_index');
            $table->dropColumn('assessed_by');
        });
    }
}
