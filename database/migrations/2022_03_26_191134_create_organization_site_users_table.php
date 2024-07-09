<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationSiteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_site_user', function (Blueprint $table) {
            $table->id();
            $table->integer('organ_id')->unsigned();
            $table->bigInteger('site_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->index('user_id');
            $table->index('site_id');
            $table->index('organ_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->foreign('organ_id')->on('users')->references('id');
            $table->foreign('site_id')->on('organization_sites')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_site_user');
    }
}
