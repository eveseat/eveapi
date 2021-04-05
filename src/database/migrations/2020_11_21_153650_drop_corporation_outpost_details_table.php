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

/**
 * Class DropCorporationOutpostDetailsTable.
 */
class DropCorporationOutpostDetailsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('corporation_outpost_details');
    }

    public function down()
    {
        Schema::create('corporation_outpost_details', function (Blueprint $table) {
            $table->bigInteger('corporation_id');
            $table->bigInteger('owner_id');
            $table->bigInteger('outpost_id');
            $table->integer('system_id');
            $table->double('docking_cost_per_ship_volume');
            $table->bigInteger('office_rental_cost');
            $table->integer('type_id');
            $table->double('reprocessing_efficiency');
            $table->double('reprocessing_station_take');
            $table->bigInteger('standing_owner_id');
            $table->double('x');
            $table->double('y');
            $table->double('z');

            $table->timestamps();

            $table->primary(['corporation_id', 'outpost_id']);
            $table->index('corporation_id');
            $table->index('outpost_id');
            $table->index('system_id');
            $table->index('type_id');
            $table->index('standing_owner_id');
        });
    }
}
