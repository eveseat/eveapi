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

class CreateCorporationBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_bookmarks', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('bookmark_id');
            $table->bigInteger('folder_id')->nullable();
            $table->dateTime('created');
            $table->string('label');
            $table->text('notes');
            $table->bigInteger('location_id');
            $table->bigInteger('creator_id');

            // item
            $table->bigInteger('item_id')->nullable();
            $table->integer('type_id')->nullable();

            // coordinates
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();
            $table->bigInteger('map_id')->nullable();
            $table->string('map_name')->nullable();

            $table->primary(['corporation_id', 'bookmark_id']);
            $table->index('corporation_id');
            $table->index('folder_id');
            $table->index('creator_id');

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

        Schema::dropIfExists('corporation_bookmarks');
    }
}
