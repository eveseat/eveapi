<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

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
