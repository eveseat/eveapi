<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('corporation_id');
            $table->enum('from_type', ['agent', 'npc_corp', 'faction']);
            $table->integer('from_id');
            $table->integer('standing');

            $table->primary(['corporation_id', 'from_type', 'from_id'], 'corporation_standings_primary_key');
            $table->index('corporation_id');
            $table->index(['from_type', 'from_id']);

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

        Schema::dropIfExists('corporation_standings');
    }
}
