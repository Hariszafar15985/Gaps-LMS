<?php

use App\Models\UserDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFalseToStudentsVisiblityUserDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            /**
             * In user_documents table, where the type is not "Enrolment",
             * Modifying the "student_visibilty" value to "false"
             */
            UserDocument::where("type", "!=", "Enrolment")
                        ->update([
                            "student_visibility" => 0
                        ]);
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
