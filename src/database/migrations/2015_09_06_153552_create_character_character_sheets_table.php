<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterCharacterSheetsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheets', function (Blueprint $table) {

            $table->integer('characterID')->unique();
            $table->string('name');
            $table->integer('homeStationID');
            $table->dateTime('DoB');
            $table->string('race');
            $table->integer('bloodLineID');
            $table->string('bloodLine');
            $table->integer('ancestryID');
            $table->string('ancestry');
            $table->string('gender');
            $table->string('corporationName');
            $table->integer('corporationID');
            $table->string('allianceName')->nullable();
            $table->integer('allianceID')->nullable();
            $table->string('factionName')->nullable();
            $table->integer('factionID');
            $table->integer('cloneTypeID');
            $table->string('cloneName');
            $table->integer('cloneSkillPoints');
            $table->integer('freeSkillPoints');
            $table->integer('freeRespecs');
            $table->dateTime('cloneJumpDate');
            $table->dateTime('lastRespecDate');
            $table->dateTime('lastTimedRespec');
            $table->dateTime('remoteStationDate');
            $table->dateTime('jumpActivation');
            $table->dateTime('jumpFatigue');
            $table->dateTime('jumpLastUpdate');
            $table->decimal('balance', 30, 2)->nullable();    // Some rich bastards out there
            $table->integer('intelligence');
            $table->integer('memory');
            $table->integer('charisma');
            $table->integer('perception');
            $table->integer('willpower');

            // Indexes
            $table->primary('characterID');
            $table->index('corporationID');
            $table->index('allianceID');
            $table->index('name');

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

        Schema::drop('character_character_sheets');
    }
}
