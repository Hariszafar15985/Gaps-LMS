<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTypeColumnFromProfileNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('profile_notes', 'type')) {
            Schema::table('profile_notes', function (Blueprint $table) {
                //
                $table->dropColumn('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profile_notes', function (Blueprint $table) {
            //
        });
    }
}
