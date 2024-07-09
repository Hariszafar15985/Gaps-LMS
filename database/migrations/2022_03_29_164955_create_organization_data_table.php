<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_data', function (Blueprint $table) {
            $table->id();
            $table->integer('organ_id')->unsigned();
            $table->boolean('po_num_required')->default(false);
            $table->string('po_sequence')->nullable();
            $table->timestamps();

            $table->foreign('organ_id')->on('users')->references('id');
            $table->index('organ_id');
            $table->index('po_num_required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_data');
    }
}
