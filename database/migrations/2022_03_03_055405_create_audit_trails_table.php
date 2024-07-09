<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('organ_id')->unsigned()->nullable();
            $table->string('role_name', 25);
            $table->string('audit_type', 50);
            $table->text('description');
            $table->integer('added_by')->unsigned()->nullable()->default(null);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('organ_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->foreign('added_by')->on('users')->references('id');
            $table->index('user_id');
            $table->index('role_name');
            $table->index(['user_id', 'role_name']);
            $table->index(['organ_id', 'role_name']);
            $table->index('added_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_trails');
    }
}
