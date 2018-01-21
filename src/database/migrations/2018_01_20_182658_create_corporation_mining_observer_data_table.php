<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationMiningObserverDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_mining_observer_data', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('observer_id');
            $table->bigInteger('recorded_corporation_id');
            $table->bigInteger('character_id');
            $table->integer('type_id');

            $table->dateTime('last_updated');
            $table->bigInteger('quantity');

            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id']);
            $table->index('corporation_id');
            $table->index('observer_id');
            $table->index('recorded_corporation_id');
            $table->index('character_id');
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

        Schema::dropIfExists('corporation_mining_observer_data');
    }
}
