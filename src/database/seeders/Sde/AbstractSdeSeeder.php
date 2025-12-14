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

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Seat\Eveapi\Mapping\Sde\AbstractSdeMapping;
use Illuminate\Database\Eloquent\Model;
use stdClass;

abstract class AbstractSdeSeeder extends Seeder
{

    protected const INSERT_BATCH_SIZE = 1000;

    /**
     * Provide the SDE dump filename related to the active seeder.
     *
     * @return string
     */
    final public static function getSdeName(): string
    {
        $class_name = substr(static::class, strrpos(static::class, '\\') + 1);
        $filename = substr($class_name, 0, strpos($class_name, 'Seeder'));

        return Str::camel($filename);
    }

    protected const FILENAME = "";

    protected const IS_MULTI_SEEDER = false;

    /**
     * Download SDE file, create related table and seed it with dump.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    final public function run()
    {
        $this->createTable();

        $this->seedTable();

        $this->after();
    }

    /**
     * Get a new progress bar to display based on the
     * amount of iterations we expect to use.
     *
     * @param  $iterations
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    public function getProgressBar($iterations)
    {

        $bar = $this->command->getOutput()->createProgressBar($iterations);

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s%');

        return $bar;
    }

    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    abstract protected function getSdeTableDefinition(Blueprint $table): void;

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractSdeMapping
     */
    abstract protected function getMappingClass(): AbstractSdeMapping;

    abstract public function insert($arr);

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
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    final protected function seedTable(): void
    {
        $path = storage_path(sprintf("sde/%s", $this::FILENAME));

        if (! file_exists($path))
            throw new FileNotFoundException("Unable to retrieve $path.");

        // First open and scan to get length.. Inefficient but I want to know length
        $linecount = 0;
        $handle = fopen($path, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }

        $bar = $this->getProgressBar($linecount);

        fclose($handle);

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->command->error("Unable to open file.");
            return; //Command::FAILURE;
        }

        $lineNumber = 0;
        $records = [];

        while (($line = fgets($handle)) !== false) {

            $lineNumber++;

            // Trim any newline or whitespace
            $line = trim($line);
            if ($line === '') {
                continue; // skip empty lines
            }

            // Decode this one JSON object
            $data = json_decode($line, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Invalid JSON at line {$lineNumber}");
                continue;
            }

            if (! $this::IS_MULTI_SEEDER) {
                $records[] = $this->getMappingClass()::detail([], $data);
            } else {
                $records = array_merge($records, $this->getMappingClass()::multiDetail([], $data));
            }
            if (count($records) >= $this::INSERT_BATCH_SIZE) {
                $this->insert($records);
                $records = [];
            }

            // processing logic here
            // $this->getMappingClass()::make($this->getNewModel()->bypassReadOnly(), $data)->save();

            $bar->advance();
        }

        if (count($records) >= 0) {
                $this->insert($records);
                $records = [];
            }

        $bar->finish();
        $this->command->getOutput()->newLine();

        fclose($handle);
    }
}
