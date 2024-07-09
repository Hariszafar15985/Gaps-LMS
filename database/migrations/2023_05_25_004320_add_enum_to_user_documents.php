<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnumToUserDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            // changing type of the column "type" from "varchar" to "enum"
            // ALTER TABLE tablename MODIFY columnname INTEGER;

            DB::statement("ALTER TABLE `user_documents` MODIFY COLUMN `type` ENUM('Resume','Invoice','Enrolment','Certificate','Notice','Letter','Request')");
            // $table->enum("type", ["Resume", "Invoice", "Enrollment", "Certificate", "Notice", "Letter", "Request"])->change();
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
