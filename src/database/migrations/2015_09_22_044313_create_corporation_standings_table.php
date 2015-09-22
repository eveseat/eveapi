<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationStandingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_standings', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('corporationID');
            $table->enum('type', ['agents', 'NPCCorporations', 'factions']);
            $table->integer('fromID');
            $table->string('fromName');
            $table->float('standing');

            // Indexes
            $table->index('corporationID');
            $table->index('type');
            $table->index('fromID');

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

        Schema::drop('corporation_standings');
    }
}
