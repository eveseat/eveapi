<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationIndustryJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_industry_jobs', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('job_id');

            $table->bigInteger('installer_id');
            $table->bigInteger('facility_id');
            $table->bigInteger('location_id');
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

            $table->primary(['corporation_id', 'job_id']);
            $table->index('corporation_id');
            $table->index('installer_id');
            $table->index('location_id');
            $table->index('blueprint_id');
            $table->index('status');

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

        Schema::dropIfExists('corporation_industry_jobs');
    }
}
