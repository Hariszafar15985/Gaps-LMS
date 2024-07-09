<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacementNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('placement_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('company_name');
            $table->string('abn');
            $table->string('employer_address');
            $table->string('contact_person');
            $table->integer('phone');
            $table->enum('employment_type' , ['Full time' , 'Part time' , 'Casual' , 'Other']);
            $table->integer('pay_rate');
            $table->integer('hours_per_week');
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
        Schema::dropIfExists('placement_notes');
    }
}
