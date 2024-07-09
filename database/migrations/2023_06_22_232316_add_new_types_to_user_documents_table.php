<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewTypesToUserDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            /** Adding new enum value "Australian-Passport" */
            DB::statement("ALTER TABLE `user_documents` CHANGE type `type` ENUM('Resume','Invoice','Enrolment','Certificate','Notice','Letter','Request', 'Healthcare-Card', 'Australian-Passport', 'Driver-Licence', 'Medicare-Card', 'Australian-Citizenship-Certificate') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");

            /** Replacing the "Healthcare-Card" with "Australian-Passport" */
            DB::statement("UPDATE user_documents SET type = 'Australian-Passport' WHERE type = 'Healthcare-Card';");

            /** Removing the  "Healthcare-Card" from the enum values */
            DB::statement("ALTER TABLE `user_documents` CHANGE type `type` ENUM('Resume','Invoice','Enrolment','Certificate','Notice','Letter','Request', 'Australian-Passport', 'Driver-Licence', 'Medicare-Card', 'Australian-Citizenship-Certificate') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
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
