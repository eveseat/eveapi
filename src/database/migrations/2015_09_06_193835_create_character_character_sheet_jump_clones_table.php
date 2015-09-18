<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterCharacterSheetJumpClonesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheet_jump_clones', function (Blueprint $table) {

            $table->integer('jumpCloneID')->unique();

            $table->integer('characterID');
            $table->integer('typeID');
            $table->bigInteger('locationID');
            $table->string('cloneName')->nullable();

            // Indexes
            $table->primary('jumpCloneID');
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

        Schema::drop('character_character_sheet_jump_clones');
    }
}
