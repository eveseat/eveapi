<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterAffiliationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_affiliations', function (Blueprint $table) {

            $table->integer('characterID')->primary();
            $table->string('characterName')->nullable();
            $table->integer('corporationID');
            $table->string('corporationName')->nullable();
            $table->integer('allianceID');
            $table->string('allianceName')->nullable();
            $table->integer('factionID');
            $table->string('factionName')->nullable();

            $table->index('corporationID');
            $table->index('allianceID');
            $table->index('factionID');

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

        Schema::dropIfExists('character_affiliations');
    }
}
