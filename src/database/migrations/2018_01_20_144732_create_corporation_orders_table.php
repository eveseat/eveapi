<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_orders', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('order_id');
            $table->integer('type_id');
            $table->integer('region_id');
            $table->bigInteger('location_id');
            $table->string('range');
            $table->boolean('is_buy_order')->nullable();
            $table->double('price');
            $table->integer('volume_total');
            $table->integer('volume_remain');
            $table->dateTime('issued');
            $table->integer('min_volume')->nullable();
            $table->integer('wallet_division')->nullable();
            $table->integer('duration')->nullable();
            $table->double('escrow')->nullable();

            $table->primary(['corporation_id', 'order_id']);
            $table->index('corporation_id');

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

        Schema::dropIfExists('corporation_orders');
    }
}
