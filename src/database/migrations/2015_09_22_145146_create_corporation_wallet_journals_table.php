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

class CreateCorporationWalletJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_journals', function (Blueprint $table) {

            $table->string('hash')->unique();

            $table->integer('corporationID');
            $table->integer('accountKey');
            $table->bigInteger('refID');
            $table->dateTime('date');
            $table->integer('refTypeID');
            $table->string('ownerName1');
            $table->integer('ownerID1');
            $table->string('ownerName2');
            $table->integer('ownerID2');
            $table->string('argName1');
            $table->integer('argID1');
            $table->decimal('amount', 30, 2);
            $table->decimal('balance', 30, 2);
            $table->string('reason');
            $table->integer('owner1TypeID');
            $table->integer('owner2TypeID');

            // Indexes
            $table->primary('hash');
            $table->index('corporationID');
            $table->index('refID');
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

        Schema::drop('corporation_wallet_journals');
    }
}
