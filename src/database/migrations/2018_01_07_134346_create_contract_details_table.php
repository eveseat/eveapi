<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

class CreateContractDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('contract_details', function (Blueprint $table) {

            $table->bigInteger('contract_id')->primary();
            $table->bigInteger('issuer_id');
            $table->bigInteger('issuer_corporation_id');
            $table->bigInteger('assignee_id');
            $table->bigInteger('acceptor_id');
            $table->bigInteger('start_location_id')->nullable();
            $table->bigInteger('end_location_id')->nullalbe();
            $table->enum('type', [
                'unknown', 'item_exchange', 'auction', 'courier', 'loan',
            ]);
            $table->enum('status', [
                'outstanding', 'in_progress', 'finished_issuer', 'finished_contractor',
                'finished', 'cancelled', 'rejected', 'failed', 'deleted', 'reversed',
            ]);
            $table->string('title')->nullable();
            $table->boolean('for_corporation');
            $table->enum('availability', ['public', 'personal', 'corporation', 'alliance']);
            $table->dateTime('date_issued');
            $table->dateTime('date_expired');
            $table->dateTime('date_accepted')->nullable();
            $table->integer('days_to_complete')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->double('price')->nullable();
            $table->double('reward')->nullable();
            $table->double('collateral')->nullable();
            $table->double('buyout')->nullable();
            $table->double('volume')->nullable();

            $table->index('issuer_id');
            $table->index('issuer_corporation_id');
            $table->index('assignee_id');
            $table->index('acceptor_id');
            $table->index('availability');
            $table->index('date_issued');
            $table->index('date_expired');

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

        Schema::dropIfExists('contract_details');
    }
}
