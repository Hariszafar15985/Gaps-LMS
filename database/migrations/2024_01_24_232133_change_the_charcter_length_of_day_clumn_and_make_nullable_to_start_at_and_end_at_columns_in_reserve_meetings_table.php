<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTheCharcterLengthOfDayClumnAndMakeNullableToStartAtAndEndAtColumnsInReserveMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserve_meetings', function (Blueprint $table) {
            $table->string('day', 255)->change();
            $table->unsignedBigInteger('start_at')->nullable()->change();
            $table->unsignedBigInteger('end_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserve_meetings', function (Blueprint $table) {
            $table->string('day', 10)->change();
            $table->unsignedBigInteger('start_at')->nullable(false)->change();
            $table->unsignedBigInteger('end_at')->nullable(false)->change();
        });
    }
}
