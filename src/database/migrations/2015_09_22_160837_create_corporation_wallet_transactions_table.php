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

class CreateCorporationWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_transactions', function (Blueprint $table) {

            $table->string('hash')->unique();
            $table->integer('corporationID');
            $table->integer('accountKey');

            $table->dateTime('transactionDateTime');
            $table->bigInteger('transactionID');
            $table->integer('quantity');
            $table->string('typeName');
            $table->integer('typeID');
            $table->decimal('price', 30, 2);
            $table->integer('clientID');
            $table->string('clientName');
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('stationID');
            $table->string('stationName');
            $table->enum('transactionType', ['buy', 'sell']);
            $table->enum('transactionFor', ['personal', 'corporation']);
            $table->bigInteger('journalTransactionID');
            $table->integer('clientTypeID');

            // Indexes
            $table->primary('hash');
            $table->index('corporationID');
            $table->index('transactionID');
            $table->index('accountKey');

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

        Schema::drop('corporation_wallet_transactions');
    }
}
