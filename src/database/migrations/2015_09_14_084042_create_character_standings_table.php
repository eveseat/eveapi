<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterStandingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_standings', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('characterID');
            $table->enum('type', ['agents', 'NPCCorporations', 'factions']);
            $table->integer('fromID');
            $table->string('fromName');
            $table->float('standing');

            // Indexes
            $table->index('characterID');
            $table->index('type');
            $table->index('fromID');

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

        Schema::drop('character_standings');
    }
}
