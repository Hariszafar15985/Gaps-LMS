<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_information', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->string('title', 5)->comment('Mr, Mrs, Miss, Dr')->nullable();
            $table->string('first_name', 30)->nullable();
            $table->string('middle_name', 30)->nullable();
            $table->string('sur_name', 30)->nullable();
            $table->date('dob')->nullable();
            $table->tinyInteger('gender')->comment('1 = Male, 2 = Female, 3 = Unspecified')->nullable();
            $table->string('suburb', 30)->nullable();
            $table->string('state', 30)->nullable();
            $table->string('post_code', 15)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->string('contact_number', 15)->nullable();
            $table->tinyInteger('send_result_to_employer')->comment('1 = Yes, 2 = No')->nullable();
            $table->tinyInteger('cultural_identity')->comment('1 = Aboriginal, 2 = Torres Strait Islander, 3 = Both, 4 = Neither')->nullable();
            $table->string('birth_country', 30)->nullable();
            $table->string('birth_city', 30)->nullable();
            $table->tinyInteger('citizenship')->comment('1 = Australia, 2 = Permanent Resident, 3 = New Zealand Resident, 4 = Other')->nullable();
            $table->string('other_visa_type', 30)->comment('other citizenship type')->nullable();
            $table->tinyInteger('does_speak_other_language')->comment('1 = Yes, 2 = No')->nullable(); // other language than english
            $table->string('other_language', 30)->comment('if you speak other language than english')->nullable();
            $table->tinyInteger('require_assistance')->comment('1 = Yes, 2 = No')->nullable();
            $table->tinyInteger('is_disable')->comment('1 = Yes, 2 = No')->nullable();
            $table->tinyInteger('disability')->comment('disability if is_disable yes')->nullable();
            $table->tinyInteger('employment_type')->comment('')->nullable();
            $table->tinyInteger('attending_secondary_school')->comment('1 = Yes, 2 = No')->nullable();
            $table->tinyInteger('school_level')->comment('Highest completed school level')->nullable();
            $table->string('school_completed_year', 4)->comment('Year completed school')->nullable();
            $table->tinyInteger('is_enrolled')->comment('is enrolled in any studies: 1 = Yes, 2 = No')->nullable();
            $table->string('enrolled_studies', 30)->comment('')->nullable();
            $table->tinyInteger('completed_qualification_in_australia')->comment('1 = Yes, 2 = No')->nullable();
            $table->tinyInteger('certificate1')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('certificate1_qualification', 30)->comment('')->nullable();
            $table->string('certificate1_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('certificate2')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('certificate2_qualification', 30)->comment('')->nullable();
            $table->string('certificate2_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('certificate3')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('certificate3_qualification', 30)->comment('')->nullable();
            $table->string('certificate3_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('certificate4')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('certificate4_qualification', 30)->comment('')->nullable();
            $table->string('certificate4_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('diploma')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('diploma_qualification', 30)->comment('')->nullable();
            $table->string('diploma_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('adiploma')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('adiploma_qualification', 30)->comment('')->nullable();
            $table->string('adiploma_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('bachelor')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('bachelor_qualification', 30)->comment('')->nullable();
            $table->string('bachelor_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('miscellaneous')->comment('1 = Yes, 0 = No')->default('0');
            $table->string('miscellaneous_qualification', 30)->comment('')->nullable();
            $table->string('miscellaneous_year_completed', 4)->comment('')->nullable();
            $table->tinyInteger('study_reason')->comment('')->nullable();
            $table->string('usi_number', 30)->comment('')->nullable();
            $table->tinyInteger('can_gaps_search_usi')->comment('1 = Yes, 0 = No')->default('0');
            $table->tinyInteger('rto_permission')->comment('1 = Yes, 0 = No')->default('0');
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
        Schema::dropIfExists('user_information');
    }
}
