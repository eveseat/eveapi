<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterPlanetContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_planet_contents', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('planet_id');
            $table->bigInteger('pin_id');
            $table->integer('type_id');
            $table->integer('amount');

            $table->primary(['character_id', 'planet_id', 'pin_id', 'type_id'], 'character_planet_contents_primary_key');
            $table->index('character_id');
            $table->index('planet_id');
            $table->index('pin_id');
            $table->index('type_id');

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

        Schema::dropIfExists('character_planet_contents');
    }
}
