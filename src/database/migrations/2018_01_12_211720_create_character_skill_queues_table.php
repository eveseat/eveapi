<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('character_id');
            $table->integer('skill_id');
            $table->dateTime('finish_date')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->integer('finished_level');
            $table->integer('queue_position');
            $table->integer('training_start_sp')->nullable();
            $table->integer('level_end_sp')->nullable();
            $table->integer('level_start_sp')->nullable();

            $table->primary(['character_id', 'skill_id']);
            $table->index('character_id');
            $table->index('queue_position');

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

        Schema::dropIfExists('character_skill_queues');
    }
}
