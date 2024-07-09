<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->string('title', 60)->document('Document title');
            $table->string('type', 20)->comment('Resume, Invoice, Enrollment, Certificate, Notice, Letter, Request');
            $table->string('description', 100)->nullable();
            $table->string('document', 60)->comment('uploaded document file name');
            $table->integer('uploaded_by')->comment('user id who uploaded document')->index();
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
        Schema::dropIfExists('user_documents');
    }
}
