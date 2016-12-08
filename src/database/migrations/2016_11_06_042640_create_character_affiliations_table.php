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

            $table->bigInteger('characterID')->primary();
            $table->string('characterName')->nullable();
            $table->bigInteger('corporationID');
            $table->string('corporationName')->nullable();
            $table->bigInteger('allianceID');
            $table->string('allianceName')->nullable();
            $table->bigInteger('factionID');
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
