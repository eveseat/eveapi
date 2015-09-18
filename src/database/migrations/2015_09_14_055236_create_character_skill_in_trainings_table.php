<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterSkillInTrainingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_skill_in_trainings', function (Blueprint $table) {

            $table->integer('characterID')->unique();
            $table->dateTime('currentTQTime')->nullable();
            $table->dateTime('trainingEndTime')->nullable();
            $table->dateTime('trainingStartTime')->nullable();
            $table->integer('trainingTypeID')->nullable();
            $table->integer('trainingStartSP')->nullable();
            $table->integer('trainingDestinationSP')->nullable();
            $table->integer('trainingToLevel')->nullable();
            $table->boolean('skillInTraining');

            // Indexes
            $table->primary('characterID');

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

        Schema::drop('character_skill_in_trainings');
    }
}
