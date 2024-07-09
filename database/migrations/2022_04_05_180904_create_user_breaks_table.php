<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_breaks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->date('from');
            $table->date('to');
            $table->enum('status', ['approved', 'canceled', 'pending', 'rejected']);
            $table->enum('type', ['casual', 'marriage', 'other', 'sick']);
            $table->text('description')->nullable();
            $table->integer('requested_by')->unsigned()->nullable();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'from', 'to']);
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->index('user_id');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index('from');
            $table->index('to');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_breaks');
    }
}
