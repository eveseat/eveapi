<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterJumpClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_jump_clones', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('jump_clone_id');
            $table->string('name')->nullable();
            $table->bigInteger('location_id');
            $table->enum('location_type', ['station', 'structure']);
            $table->json('implants');

            $table->primary(['character_id', 'jump_clone_id']);
            $table->index('location_id');

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

        Schema::dropIfExists('character_jump_clones');
    }
}
