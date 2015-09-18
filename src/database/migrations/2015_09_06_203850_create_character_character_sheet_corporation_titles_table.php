<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterCharacterSheetCorporationTitlesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheet_corporation_titles', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('characterID');
            $table->integer('titleID');
            $table->string('titleName');

            // Index
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

        Schema::drop('character_character_sheet_corporation_titles');
    }
}
