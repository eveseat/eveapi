<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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
 * Class DropCorporationOutpostServicesTable.
 */
class DropCorporationOutpostServicesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('corporation_outpost_services');
    }

    public function down()
    {
        Schema::create('corporation_outpost_services', function (Blueprint $table) {
            $table->bigInteger('corporation_id');
            $table->bigInteger('outpost_id');
            $table->string('service_name');
            $table->double('minimum_standing');
            $table->double('surcharge_per_bad_standing');
            $table->double('discount_per_good_standing');

            $table->timestamps();

            $table->primary(['corporation_id', 'outpost_id', 'service_name'], 'corporation_outposts_primary_key');
            $table->index('corporation_id');
            $table->index('outpost_id');
        });
    }
}
