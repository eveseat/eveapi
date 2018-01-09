<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterFatiguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_fatigues', function (Blueprint $table) {

            $table->bigInteger('character_id')->primary();

            $table->dateTime('last_jump_date')->nullable();
            $table->dateTime('jump_fatigue_expire_date')->nullable();
            $table->dateTime('last_update_date')->nullable();

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

        Schema::dropIfExists('character_fatigues');
    }
}
