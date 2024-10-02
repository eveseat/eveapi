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

namespace Seat\Eveapi\Database\Seeders\Sde;

use Illuminate\Database\Schema\Blueprint;
use Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping;
use Seat\Eveapi\Mapping\Sde\InvTypeMapping;

class InvTypesSeeder extends AbstractSdeSeeder
{
    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    protected function getSdeTableDefinition(Blueprint $table): void
    {
        $table->integer('typeID')->primary();
        $table->integer('groupID');
        $table->string('typeName', 100);
        $table->text('description')->nullable();
        $table->double('mass')->nullable();
        $table->double('volume')->nullable();
        $table->double('capacity')->nullable();
        $table->integer('portionSize')->nullable();
        $table->integer('raceID')->nullable();
        $table->double('basePrice')->nullable();
        $table->boolean('published')->default(false);
        $table->integer('marketGroupID')->nullable();
        $table->integer('iconID')->nullable();
        $table->integer('graphicID')->nullable();
    }

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping
     */
    protected function getMappingClass(): AbstractFuzzworkMapping
    {
        return new InvTypeMapping();
    }
}
