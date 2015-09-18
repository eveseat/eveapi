<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterSkillQueuesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_skill_queues', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('queuePosition');
            $table->integer('typeID');
            $table->integer('level');
            $table->integer('startSP');
            $table->integer('endSP');
            $table->dateTime('startTime')->nullable(); // If current queue is paused this will be null
            $table->dateTime('endTime')->nullable(); // If current queue is paused this will be null

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

        Schema::drop('character_skill_queues');
    }
}
