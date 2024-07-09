<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXeroContactIdColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('xero_contact_id', 40)->nullable();
            
            $table->index('xero_contact_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'xero_contact_id')) {
            Schema::table('users', function (Blueprint $table) {
                //
                $table->dropColumn('xero_contact_id');
            });
        }
    }
}
