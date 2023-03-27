<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

/*
Do not rename this migration or it will break the update process for seat 4 installs using recursivetree/seat-treelib.
Since the original PR was aimed at seat 4 and the delayed to seat 5, it was in the meantime included in seat 4 via seat-treelib.
*/
class AddMarketOrdersData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_orders', function (Blueprint $table) {
            // just mirror the ESI data
            $table->bigInteger('order_id')->unsigned()->primary();
            $table->smallInteger('duration')->unsigned();
            $table->boolean('is_buy_order');
            $table->dateTime('issued');
            $table->dateTime('expiry');
            $table->bigInteger('location_id');
            $table->integer('min_volume')->unsigned();
            $table->decimal('price', 30, 2);
            $table->enum('range', ['station', 'region', 'solarsystem', '1', '2', '3', '4', '5', '10', '20', '30', '40']);
            $table->bigInteger('system_id')->unsigned();
            $table->bigInteger('type_id')->unsigned()->index();
            $table->integer('volume_remaining')->unsigned();
            $table->integer('volume_total')->unsigned();
        });

        Schema::table('market_prices', function (Blueprint $table) {
            $table->decimal('sell_price', 30, 2)->default(0);
            $table->decimal('buy_price', 30, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('market_orders');

        Schema::table('market_prices', function (Blueprint $table) {
            $table->dropColumn('sell_price');
            $table->dropColumn('buy_price');
        });
    }
}
