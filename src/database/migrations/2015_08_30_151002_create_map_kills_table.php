<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMapKillsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('map_kills', function (Blueprint $table) {

            $table->integer('solarSystemID')->unique();
            $table->integer('shipKills');
            $table->integer('factionKills');
            $table->integer('podKills');

            // Index
            $table->primary('solarSystemID');

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

        Schema::drop('map_kills');
    }
}
