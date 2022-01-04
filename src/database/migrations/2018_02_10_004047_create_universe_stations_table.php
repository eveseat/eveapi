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

class CreateUniverseStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('universe_stations', function (Blueprint $table) {

            $table->integer('station_id');
            $table->integer('type_id');
            $table->string('name');
            $table->bigInteger('owner')->nullable();
            $table->integer('race_id')->nullable();
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->integer('system_id');
            $table->float('reprocessing_efficiency');
            $table->float('reprocessing_stations_take');
            $table->float('max_dockable_ship_volume', 10, 2);
            $table->float('office_rental_cost', 12, 2);

            $table->primary('station_id');
            $table->index('type_id');
            $table->index('system_id');

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

        Schema::dropIfExists('universe_stations');
    }
}
