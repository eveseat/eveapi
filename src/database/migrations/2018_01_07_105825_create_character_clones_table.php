<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_clones', function (Blueprint $table) {

            $table->integer('character_id')->primary();
            $table->dateTime('last_clone_jump_date')->nullable();
            $table->bigInteger('home_location_id')->nullable();
            $table->enum('home_location_type', ['station', 'structure'])->nullable();
            $table->dateTime('last_station_change_date')->nullable();

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

        Schema::dropIfExists('character_clones');
    }
}
