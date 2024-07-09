<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDeclarationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_declarations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('student_name');
            $table->date('signed_on')->default(date('Y-m-d'));
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->index('signed_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_declarations');
    }
}
