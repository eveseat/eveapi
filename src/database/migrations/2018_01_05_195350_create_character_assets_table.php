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

class CreateCharacterAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_assets', function (Blueprint $table) {

            $table->bigInteger('item_id')->primary();
            $table->bigInteger('character_id');
            $table->integer('type_id');
            $table->integer('quantity');
            $table->bigInteger('location_id');
            $table->enum('location_type', ['station', 'solar_system', 'other']);
            $table->string('location_flag');
            $table->boolean('is_singleton');

            // location information
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();
            $table->bigInteger('map_id')->nullable();
            $table->string('map_name')->nullable();

            // name
            $table->string('name')->nullable();

            $table->index('character_id');
            $table->index('location_id');
            $table->index('location_type');

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

        Schema::dropIfExists('character_assets');
    }
}
