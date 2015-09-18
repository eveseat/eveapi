<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterResearchesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_researches', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('characterID');
            $table->integer('agentID');
            $table->integer('skillTypeID');
            $table->dateTime('researchStartDate');
            $table->float('pointsPerDay');
            $table->float('remainderPoints');

            // Indexes
            $table->index('characterID');

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

        Schema::drop('character_researches');
    }
}
