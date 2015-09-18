<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterCharacterSheetSkillsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheet_skills', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->integer('typeID');
            $table->integer('skillpoints');
            $table->integer('level');
            $table->integer('published');

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

        Schema::drop('character_character_sheet_skills');
    }
}
