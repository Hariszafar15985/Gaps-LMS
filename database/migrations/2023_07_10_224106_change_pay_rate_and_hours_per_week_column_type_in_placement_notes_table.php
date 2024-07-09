<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePayRateAndHoursPerWeekColumnTypeInPlacementNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('placement_notes', function (Blueprint $table) {
            $table->decimal('pay_rate', 10, 2)->change();
            $table->decimal('hours_per_week', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('placement_notes', function (Blueprint $table) {
            $table->integer('pay_rate')->change();
            $table->integer('hours_per_week')->change();
        });
    }
}
