<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterAgentResearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_agent_researches', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('agent_id');

            $table->integer('skill_type_id');
            $table->dateTime('started_at');
            $table->float('points_per_day');
            $table->float('remainder_points');

            $table->primary(['character_id', 'agent_id']);

            $table->index('character_id');
            $table->index('agent_id');

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

        Schema::dropIfExists('character_agent_researches');
    }
}
