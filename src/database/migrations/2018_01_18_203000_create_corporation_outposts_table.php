<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationOutpostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_outposts', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('owner_id');
            $table->bigInteger('outpost_id');
            $table->integer('system_id');
            $table->float('docking_cost_per_ship_volume');
            $table->bigInteger('office_rental_cost');
            $table->integer('type_id');
            $table->float('reprocessing_efficiency');
            $table->float('reprocessing_station_take');
            $table->bigInteger('standing_owner_id');
            $table->double('x');
            $table->double('y');
            $table->double('z');

            $table->primary(['corporation_id', 'outpost_id'], 'corporation_outposts_primary_key');
            $table->index('corporation_id');
            $table->index('outpost_id');
            $table->index('system_id');
            $table->index('type_id');
            $table->index('standing_owner_id');

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

        Schema::dropIfExists('corporation_outposts');
    }
}
