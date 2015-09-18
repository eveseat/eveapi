<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterCharacterSheetJumpCloneImplantsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_character_sheet_jump_clone_implants', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('jumpCloneID');
            $table->integer('characterID'); // Needed to clear out in character_sheet update
            $table->integer('typeID');
            $table->string('typeName');

            // Indexes
            $table->index('jumpCloneID');

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

        Schema::drop('character_character_sheet_jump_clone_implants');
    }
}
