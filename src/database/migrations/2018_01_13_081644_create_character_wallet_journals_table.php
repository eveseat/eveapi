<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

class CreateCharacterWalletJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_wallet_journals', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('id');
            $table->dateTime('date');
            $table->string('ref_type');
            $table->bigInteger('first_party_id')->nullable();
            $table->bigInteger('second_party_id')->nullable();
            $table->double('amount')->nullable();
            $table->double('balance')->nullable();
            $table->text('reason')->nullable();
            $table->bigInteger('tax_receiver_id')->nullable();
            $table->double('tax')->nullable();
            // introduced with version 4
            $table->bigInteger('context_id')->nullable();
            $table->enum('context_id_type',
                ['structure_id', 'station_id', 'market_transaction_id', 'character_id', 'corporation_id', 'alliance_id',
                    'eve_system', 'industry_job_id', 'contract_id', 'planet_id', 'system_id', 'type_id', ])->nullable();
            $table->string('description');

            $table->primary(['character_id', 'id']);
            $table->index('character_id');
            $table->index('id');
            $table->index('date');

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

        Schema::dropIfExists('character_wallet_journals');
    }
}
