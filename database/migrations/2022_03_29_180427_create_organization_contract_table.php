<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_contracts', function (Blueprint $table) {
            $table->id();
            $table->integer('organ_id')->unsigned();
            $table->enum('contract', ['DES', 'Jobactive', 'TTW', 'Parents', 'Next', 'Other']);
            $table->string('other_contract', 250)->nullable();

            $table->unique(['organ_id', 'contract']);
            $table->foreign('organ_id')->on('users')->references('id')->onDelete('cascade');
            $table->index('organ_id');
            $table->index('contract');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_contracts');
    }
}
