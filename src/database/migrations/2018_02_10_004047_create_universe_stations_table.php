<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniverseStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('universe_stations', function (Blueprint $table) {

            $table->integer('station_id');
            $table->integer('type_id');
            $table->string('name');
            $table->bigInteger('owner')->nullable();
            $table->integer('race_id')->nullable();
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->integer('system_id');
            $table->float('reprocessing_efficiency');
            $table->float('reprocessing_stations_take');
            $table->float('max_dockable_ship_volume', 10, 2);
            $table->float('office_rental_cost', 12, 2);

            $table->primary('station_id');
            $table->index('type_id');
            $table->index('system_id');

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

        Schema::dropIfExists('universe_stations');
    }
}
