<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_assets', function (Blueprint $table) {

            $table->bigInteger('item_id')->primary();
            $table->bigInteger('corporation_id');
            $table->integer('type_id');
            $table->integer('quantity');
            $table->bigInteger('location_id');
            $table->enum('location_type', ['station', 'solar_system', 'other']);
            $table->string('location_flag');
            $table->boolean('is_singleton');

            // location information
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();
            $table->bigInteger('map_id')->nullable();
            $table->string('map_name')->nullable();

            // name
            $table->string('name')->nullable();

            $table->index('corporation_id');
            $table->index('location_id');
            $table->index('location_type');

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

        Schema::dropIfExists('corporation_assets');
    }
}
