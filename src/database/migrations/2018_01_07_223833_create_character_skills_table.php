<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_skills', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->integer('skill_id');
            $table->integer('skillpoints_in_skill');
            $table->integer('trained_skill_level');
            $table->integer('active_skill_level');

            $table->primary(['character_id', 'skill_id']);
            $table->index('character_id');
            $table->index('skill_id');

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

        Schema::dropIfExists('character_skills');
    }
}
