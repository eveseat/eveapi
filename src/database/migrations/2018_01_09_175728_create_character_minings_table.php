<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterMiningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_minings', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->dateTime('date');
            $table->integer('solar_system_id');
            $table->integer('type_id');
            $table->bigInteger('quantity');

            $table->primary(['character_id', 'date', 'solar_system_id', 'type_id'],
                'mining_primary');
            $table->index('character_id');
            $table->index('date');
            $table->index('solar_system_id');
            $table->index('type_id');

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

        Schema::dropIfExists('character_minings');
    }
}
