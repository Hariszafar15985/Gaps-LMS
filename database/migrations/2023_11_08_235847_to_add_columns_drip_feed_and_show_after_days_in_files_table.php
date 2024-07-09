<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ToAddColumnsDripFeedAndShowAfterDaysInFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->tinyInteger('drip_feed')->default(0)->after('status');
            $table->smallInteger('show_after_days')->default(NULL)->after('drip_feed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('drip_feed');
            $table->dropColumn('show_after_days');
        });
    }
}
