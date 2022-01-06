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
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Database\Seeders\Sde;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping;

abstract class AbstractSdeSeeder extends Seeder
{
    /**
     * Provide the SDE dump filename related to the active seeder.
     *
     * @return string
     */
    final static public function getSdeName(): string
    {
        $class_name = substr(static::class, strrpos(static::class, '\\') + 1);
        $filename = substr($class_name, 0, strpos($class_name, 'Seeder'));

        return Str::camel($filename);
    }

    /**
     * Download SDE file, create related table and seed it with dump.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    final public function run()
    {
        $this->createTable();

        $this->seedTable();

        $this->after();
    }

    /**
     * Define seeder related SDE table structure.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @return void
     */
    abstract protected function getSdeTableDefinition(Blueprint $table): void;

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping
     */
    abstract protected function getMappingClass(): AbstractFuzzworkMapping;

    /**
     * Determine actions which have to be executed after the seeding process.
     *
     * @return void
     */
    protected function after(): void
    {
        // override this method if you need your seeder to do extra things.
    }

    /**
     * Create seeder related SDE table.
     *
     * @return void
     */
    final protected function createTable(): void
    {
        Schema::dropIfExists($this->getSdeName());

        Schema::create($this->getSdeName(), function (Blueprint $table) {
            $this->getSdeTableDefinition($table);
        });
    }

    /**
     * Use SDE dump related to the active seeder in order to seed table.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    final protected function seedTable(): void
    {
        $sde_dump = $this->getSdeName();
        $path = storage_path("sde/$sde_dump.csv");

        if (! file_exists($path))
            throw new FileNotFoundException("Unable to retrieve $path.");

        Excel::import($this->getMappingClass(), $path);
    }
}
