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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Seat\Eveapi\Mapping\Sde\AbstractSdeMapping;

abstract class AbstractSdeSeeder extends Seeder
{

    protected const INSERT_BATCH_SIZE = 500;

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

    protected const FILENAME = '';

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
        try {
            $this->disableFK();

            $this->restoreBackup();

            $this->backupTable();

            $this->createTable();

            $this->seedTable();

            $this->restoreExtraIndexes();

            $this->after();

            $this->dropBackup();
        } catch (\Throwable $e) {
            $this->restoreBackup();

            throw $e;
        } finally {
            $this->enableFK(); // Really dont want to leave this disabled.
        }
    }

    /**
     * Name the last complete copy of the table is kept under while it is reseeded.
     *
     * @return string
     */
    final public static function getBackupName(): string
    {
        return static::getSdeName() . '_backup';
    }

    /**
     * The backup relies on CREATE TABLE ... LIKE and information_schema, so other
     * drivers keep the plain drop and recreate.
     *
     * @return bool
     */
    private function canKeepBackup(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    /**
     * Copy the live table rather than renaming it: a rename would carry other tables'
     * foreign keys over to the backup and leave them pointing at it once it is dropped.
     * The copy is filled under a temporary name and renamed when complete, so a backup
     * is never half filled.
     *
     * @return void
     */
    private function backupTable(): void
    {
        if (! $this->canKeepBackup() || ! Schema::hasTable($this->getSdeName()))
            return;

        $copy = $this->getBackupName() . '_new';

        Schema::dropIfExists($copy);
        DB::statement(sprintf('CREATE TABLE `%s` LIKE `%s`', $copy, $this->getSdeName()));
        DB::statement(sprintf('INSERT INTO `%s` SELECT * FROM `%s`', $copy, $this->getSdeName()));
        Schema::rename($copy, $this->getBackupName());
    }

    /**
     * A backup only exists while a seed is running or after one was killed part way,
     * so whenever one is found it is the last complete copy of the table.
     *
     * @return void
     */
    private function restoreBackup(): void
    {
        if (! $this->canKeepBackup())
            return;

        Schema::dropIfExists($this->getBackupName() . '_new');

        if (! Schema::hasTable($this->getBackupName()))
            return;

        Schema::dropIfExists($this->getSdeName());
        Schema::rename($this->getBackupName(), $this->getSdeName());
    }

    /**
     * Put back indexes that operators or plugins added to the table, since the table
     * definition above only knows about its own.
     *
     * @return void
     */
    private function restoreExtraIndexes(): void
    {
        if (! $this->canKeepBackup() || ! Schema::hasTable($this->getBackupName()))
            return;

        $current = $this->getIndexes($this->getSdeName());

        foreach ($this->getIndexes($this->getBackupName()) as $name => $columns) {
            if ($name === 'PRIMARY' || $current->has($name))
                continue;

            $missing = $columns->pluck('COLUMN_NAME')
                ->reject(fn ($column) => Schema::hasColumn($this->getSdeName(), $column));

            if ($missing->isNotEmpty()) {
                $this->command->warn(sprintf('Index %s on %s was not restored, column %s no longer exists.',
                    $name, $this->getSdeName(), $missing->implode(', ')));
                continue;
            }

            $first = $columns->first();
            $kind = $first->INDEX_TYPE === 'FULLTEXT' ? 'FULLTEXT ' : ($first->NON_UNIQUE == 0 ? 'UNIQUE ' : '');
            $parts = $columns->map(fn ($column) => sprintf('`%s`%s', $column->COLUMN_NAME,
                $column->SUB_PART ? '(' . (int) $column->SUB_PART . ')' : ''));

            try {
                DB::statement(sprintf('CREATE %sINDEX `%s` ON `%s` (%s)',
                    $kind, $name, $this->getSdeName(), $parts->implode(', ')));
            } catch (\Throwable $e) {
                // A unique index the new data no longer satisfies should not throw away a good seed.
                $this->command->warn(sprintf('Index %s on %s was not restored: %s',
                    $name, $this->getSdeName(), $e->getMessage()));
            }
        }
    }

    /**
     * @param  string  $table
     * @return \Illuminate\Support\Collection
     */
    private function getIndexes(string $table)
    {
        return collect(DB::select(
            'SELECT INDEX_NAME, NON_UNIQUE, INDEX_TYPE, COLUMN_NAME, SUB_PART FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            [DB::connection()->getDatabaseName(), $table]
        ))->groupBy('INDEX_NAME');
    }

    private function dropBackup(): void
    {
        if ($this->canKeepBackup())
            Schema::dropIfExists($this->getBackupName());
    }

    private function disableFK()
    {
        Schema::disableForeignKeyConstraints();
    }

    private function enableFK()
    {
        Schema::enableForeignKeyConstraints();
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
        $path = storage_path(sprintf('sde/%s', $this::FILENAME));

        if (! file_exists($path))
            throw new FileNotFoundException("Unable to retrieve $path.");

        // First open and scan to get length.. Inefficient but I want to know length
        $linecount = 0;
        $handle = fopen($path, 'r');
        while (! feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }

        $bar = $this->getProgressBar($linecount);

        fclose($handle);

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->command->error('Unable to open file.');

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

        if (count($records) > 0) {
            $this->insert($records);
            $records = [];
        }

        $bar->finish();
        $this->command->getOutput()->newLine();

        fclose($handle);
    }
}
