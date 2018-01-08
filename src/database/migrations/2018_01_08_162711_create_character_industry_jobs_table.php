<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterIndustryJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_industry_jobs', function (Blueprint $table) {

            $table->integer('character_id');
            $table->integer('job_id');

            $table->integer('installer_id');
            $table->bigInteger('facility_id');
            $table->bigInteger('station_id');
            $table->integer('activity_id');
            $table->bigInteger('blueprint_id');
            $table->integer('blueprint_type_id');
            $table->bigInteger('blueprint_location_id');
            $table->bigInteger('output_location_id');
            $table->integer('runs');
            $table->double('cost')->nullable();
            $table->integer('licensed_runs')->nullable();
            $table->float('probability')->nullable();
            $table->integer('product_type_id')->nullable();
            $table->enum('status', ['active', 'cancelled', 'delivered', 'paused', 'ready', 'reverted']);
            $table->integer('duration');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('pause_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->integer('completed_character_id')->nullable();
            $table->integer('successful_runs')->nullable();

            $table->primary(['character_id', 'job_id']);
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

        Schema::dropIfExists('character_industry_jobs');
    }
}
