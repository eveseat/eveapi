<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

            $table->bigInteger('corporation_id');
            $table->integer('division');
            $table->bigInteger('transaction_id');
            $table->dateTime('date');
            $table->integer('type_id');
            $table->bigInteger('location_id');
            $table->double('unit_price');
            $table->integer('quantity');
            $table->integer('client_id');
            $table->boolean('is_buy');
            $table->bigInteger('journal_ref_id');

            $table->primary(['corporation_id', 'division', 'transaction_id'],
                'corporation_wallet_transactions_primary_key');
            $table->index('corporation_id');
            $table->index(['corporation_id', 'division']);
            $table->index('transaction_id');
            $table->index('journal_ref_id');

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

        Schema::dropIfExists('corporation_wallet_transactions');
    }
}
