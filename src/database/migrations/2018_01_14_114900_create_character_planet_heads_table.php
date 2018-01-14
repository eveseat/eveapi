<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_heads', function (Blueprint $table) {

            $table->bigInteger('character_id');
	        $table->integer('planet_id');
	        $table->bigInteger('extractor_id');
	        $table->integer('head_id');
	        $table->float('latitude');
	        $table->float('longitude');

            $table->primary(['character_id', 'planet_id', 'extractor_id', 'head_id'], 'character_planet_heads_primary_key');
            $table->index('character_id');
            $table->index('planet_id');
            $table->index('extractor_id');

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

        Schema::dropIfExists('character_planet_heads');
    }
}
