<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_attributes', function (Blueprint $table) {

            $table->bigInteger('character_id')->primary();
            $table->integer('charisma');
            $table->integer('intelligence');
            $table->integer('memory');
            $table->integer('perception');
            $table->integer('willpower');
            $table->integer('bonus_remaps')->nullable();
            $table->dateTime('last_remap_date')->nullable();
            $table->dateTime('accrued_remap_cooldown_date')->nullable();
           
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

        Schema::dropIfExists('character_attributes');
    }
}
