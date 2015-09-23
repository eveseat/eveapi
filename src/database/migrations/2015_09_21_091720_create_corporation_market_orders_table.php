<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMarketOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_market_orders', function (Blueprint $table) {

            $table->bigInteger('orderID')->unique();
            $table->integer('corporationID');
            $table->integer('charID');
            $table->integer('stationID');
            $table->integer('volEntered');
            $table->integer('volRemaining');
            $table->integer('minVolume');
            $table->integer('orderState');
            $table->integer('typeID');
            $table->integer('range');
            $table->integer('accountKey');
            $table->integer('duration');
            $table->decimal('escrow', 30, 2);
            $table->decimal('price', 30, 2);
            $table->integer('bid');
            $table->dateTime('issued');

            // Indexes
            $table->primary('orderID');
            $table->index('corporationID');
            $table->index('charID');

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

        Schema::drop('corporation_market_orders');
    }
}
