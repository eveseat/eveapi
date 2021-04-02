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
 * Class CreateInvTypesTable.
 */
class CreateInvTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('invTypes', function (Blueprint $table) {

            $table->integer('typeID')->primary();
            $table->integer('groupID')->nullable();
            $table->string('typeName')->nullable();
            $table->text('description')->nullable();
            $table->double('mass')->nullable();
            $table->double('volume')->nullable();
            $table->double('capacity')->nullable();
            $table->integer('portionSize')->nullable();
            $table->integer('raceID')->nullable();
            $table->decimal('basePrice')->nullable();
            $table->boolean('published')->nullable();
            $table->integer('marketGroupID')->nullable();
            $table->integer('iconID')->nullable();
            $table->integer('soundID')->nullable();
            $table->integer('graphicID')->nullable();

            $table->index(['groupID']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('invTypes');
    }
}
