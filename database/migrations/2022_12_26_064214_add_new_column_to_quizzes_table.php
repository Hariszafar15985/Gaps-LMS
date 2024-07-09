<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('display_limited_questions')->default(false)->after('total_mark');
            $table->integer('display_number_of_questions')->unsigned()->nullable()->after('display_limited_questions');
            $table->boolean('display_questions_randomly')->default(false)->after('display_number_of_questions');
            $table->integer('expiry_days')->unsigned()->nullable()->after('display_questions_randomly');
        });

        Schema::table('quizzes_questions', function (Blueprint $table) {
            //$table->integer('order')->unsigned()->nullable()->after('video');
            
            $table->integer('temp_order')->unsigned()->nullable()->after('order');

        });
        DB::statement("UPDATE `quizzes_questions` SET `temp_order` = CAST(`order` AS UNSIGNED)");
        DB::statement("UPDATE `quizzes_questions` SET `order` = 0");
        DB::statement("ALTER TABLE `quizzes_questions` MODIFY  COLUMN `order` INT(11) UNSIGNED DEFAULT 0");
        DB::statement("UPDATE `quizzes_questions` SET `order` = `temp_order`");
        
        Schema::table('quizzes_questions', function (Blueprint $table) {
            $table->dropColumn('temp_order');
        });
    }
}
