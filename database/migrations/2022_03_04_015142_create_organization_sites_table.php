<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('organ_id')->unsigned();
            $table->timestamps();

            $table->unique(['name', 'organ_id']);
            $table->foreign('organ_id')->on('users')->references('id');
            $table->index('organ_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_sites');
    }
}
