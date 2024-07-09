<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocsTypeToUserDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            // adding two further document types
            DB::statement("UPDATE user_documents SET type = 'Enrolment' WHERE type = 'Enrollment';");
            DB::statement("ALTER TABLE `user_documents` CHANGE `type` `type` ENUM('Resume','Invoice','Enrolment','Certificate','Notice','Letter','Request', 'HealthCare-Card', 'Driver-Licence', 'Medicare-Card') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            //
        });
    }
}
