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
            $table->boolean('is_buy_order');
            $table->double('price');
            $table->integer('volume_total');
            $table->integer('volume_remain');
            $table->dateTime('issued');
            $table->enum('state',
                ['cancelled', 'character_deleted', 'closed', 'expired', 'open', 'pending']);
            $table->integer('min_volume');
            $table->integer('division');
            $table->integer('duration');
            $table->double('escrow');

            $table->primary(['corporation_id', 'order_id'], 'corporation_orders_primary_key');
            $table->index('corporation_id');
            $table->index('type_id');
            $table->index('region_id');
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

        Schema::dropIfExists('corporation_orders');
    }
}
