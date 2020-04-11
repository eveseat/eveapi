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

class CreateCorporationIssuedMedalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_issued_medals', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('medal_id');
            $table->bigInteger('character_id');
            $table->text('reason');
            $table->enum('status', ['private', 'public']);
            $table->bigInteger('issuer_id');
            $table->dateTime('issued_at');

            $table->primary(['corporation_id', 'medal_id', 'character_id'],
                'corporation_issued_medals_primary_key');
            $table->index('corporation_id');
            $table->index('medal_id');
            $table->index('character_id');
            $table->index('issuer_id');

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

        Schema::dropIfExists('corporation_issued_medals');
    }
}
