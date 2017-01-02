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

class CreateCorporationSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_sheets', function (Blueprint $table) {

            $table->integer('corporationID')->unique();
            $table->string('corporationName');
            $table->string('ticker');
            $table->integer('ceoID');
            $table->string('ceoName');
            $table->integer('stationID');
            $table->string('stationName');
            $table->text('description');
            $table->string('url');
            $table->integer('allianceID')->nullable();
            $table->integer('factionID')->nullable();
            $table->string('allianceName')->nullable();
            $table->decimal('taxRate', 30, 2);
            $table->integer('memberCount');
            $table->integer('memberLimit');
            $table->integer('shares');
            $table->integer('graphicID');
            $table->integer('shape1');
            $table->integer('shape2');
            $table->integer('shape3');
            $table->integer('color1');
            $table->integer('color2');
            $table->integer('color3');

            // Indexes
            $table->primary('corporationID');
            $table->index('corporationName');

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

        Schema::drop('corporation_sheets');
    }
}
