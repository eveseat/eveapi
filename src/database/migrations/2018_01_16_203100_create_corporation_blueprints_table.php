<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationBlueprintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_blueprints', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('item_id');
            $table->integer('type_id');
            $table->bigInteger('location_id');
            $table->string('location_flag');
            $table->integer('quantity');
            $table->integer('time_efficiency');
            $table->integer('material_efficiency');
            $table->integer('runs');

            $table->primary(['corporation_id', 'item_id'], 'corporation_blueprints_primary_key');
            $table->index('corporation_id');
            $table->index('item_id');
            $table->index('type_id');
            $table->index('location_id');

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

        Schema::dropIfExists('corporation_blueprints');
    }
}
