<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationIndustryMiningExtractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_industry_mining_extractions', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->integer('moon_id');
            $table->dateTime('extraction_start_time');
            $table->dateTime('chunk_arrival_time');
            $table->dateTime('natural_decay_time');

            $table->primary(['corporation_id', 'structure_id'], 'extrations_primary');
            $table->index('corporation_id');
            $table->index('structure_id');
            $table->index('moon_id');

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

        Schema::dropIfExists('corporation_industry_mining_extractions');
    }
}
