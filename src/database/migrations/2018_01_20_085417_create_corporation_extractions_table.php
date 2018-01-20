<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationExtractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_extractions', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->dateTime('extraction_start_time');
            $table->integer('moon_id');
            $table->dateTime('chunk_arrival_time');
            $table->dateTime('natural_decay_time');

            $table->primary(['corporation_id', 'structure_id', 'extraction_start_time'], 'corporation_extractions_primary_key');
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

        Schema::dropIfExists('corporation_extractions');
    }
}
