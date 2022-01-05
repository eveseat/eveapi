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

namespace Seat\Eveapi\Database\Seeders\Sde;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Seat\Eveapi\Mapping\Sde\InvMarketGroupMapping;

class InvMarketGroupsSeeder extends Seeder
{
    public function run()
    {
        $this->createTable();

        $this->seedTable();
    }

    /**
     * Create mapDenormalize table structure.
     *
     * @return void
     */
    private function createTable()
    {
        Schema::dropIfExists('invMarketGroups');

        Schema::create('invMarketGroups', function (Blueprint $table) {
            $table->integer('marketGroupID')->primary();
            $table->integer('parentGroupID')->nullable();
            $table->string('marketGroupName', 100);
            $table->string('description', 250)->nullable();
            $table->integer('iconID')->nullable();
        });
    }

    /**
     * Seed table with csv content.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function seedTable()
    {
        $file = storage_path('sde/invMarketGroups.csv');

        if (! file_exists($file))
            throw new FileNotFoundException("Unable to retrieve $file.");

        Excel::import(new InvMarketGroupMapping(), $file);
    }
}
