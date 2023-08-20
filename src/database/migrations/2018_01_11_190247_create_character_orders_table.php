<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
use Illuminate\Support\Facades\Schema;

class CreateCharacterOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_orders', function (Blueprint $table) {

            $table->bigInteger('character_id');
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
            $table->integer('duration');
            $table->boolean('is_corporation');
            $table->double('escrow')->nullable();

            $table->primary(['character_id', 'order_id']);
            $table->index('character_id');

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

        Schema::dropIfExists('character_orders');
    }
}
