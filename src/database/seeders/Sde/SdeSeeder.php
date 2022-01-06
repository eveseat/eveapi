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

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Seat\Eveapi\Exception\InvalidSdeSeederException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * SdeSeeder.
 *
 * Used as a facade to seed all SDE related tables.
 */
class SdeSeeder extends Seeder
{
    /**
     * @var string
     */
    private string $sde_repository = 'https://www.fuzzwork.co.uk/dump/:version/';

    /**
     * @var string
     */
    private string $version = 'latest';

    /**
     * @throws \Seat\Eveapi\Exception\InvalidSdeSeederException
     */
    public function run()
    {
        // extract sde file/seeder mapping from config
        $sde_seeders = config('seat.sde.seeders', []);

        $this->command->info('Checking configuration...');
        if (! $this->isStorageOk())
            throw new DirectoryNotFoundException('Storage path is not OK. Please check permissions.');

        $this->command->info('Downloading static files...');
        $this->downloadStaticFiles($sde_seeders);

        $this->command->info('Seeding SDE into Database...');
        $this->call($sde_seeders);
    }

    /**
     * Download the EVE Sde from Fuzzwork and save it
     * in the storage_path/sde folder.
     *
     * @param array $seeders
     * @throws \Seat\Eveapi\Exception\InvalidSdeSeederException
     */
    private function downloadStaticFiles(array $seeders)
    {
        foreach ($seeders as $seeder) {

            if (! is_subclass_of($seeder, AbstractSdeSeeder::class))
                throw new InvalidSdeSeederException($seeder . ' must extend ' . AbstractSdeSeeder::class . 'to be able to use SDE seeder.');

            $url = $this->getSdeFileRemoteUri($seeder);
            $destination = $this->getSdeFileLocalUri($seeder);

            $file_handler = fopen($destination, 'w');

            $result = Http::withOptions([
                'sink' => $file_handler,
            ])->get($url);

            fclose($file_handler);

            if ($result->status() == 200) {
                $this->command->getOutput()->writeln("<comment>Downloaded:</comment> {$seeder::getSdeName()}");

                continue;
            }

            $this->command->error('Unable to download: ' . $url .
                '. The HTTP response was: ' . $result->status());
        }
    }

    /**
     * Check that the storage path is ok. I needed it
     * will be automatically created.
     *
     * @return bool
     */
    private function isStorageOk(): bool
    {
        $storage = storage_path('sde/');
        $this->command->getOutput()->writeln("<comment>SDE storage path is:</comment> {$storage}");

        if (File::isWritable(storage_path())) {

            // Check that the path exists
            if (! File::exists($storage))
                File::makeDirectory($storage, 0755, true);

            return true;
        }

        return false;
    }

    /**
     * Determine the SDE remote URI for the provided seeder.
     *
     * @param string $seeder
     * @return string
     */
    private function getSdeFileRemoteUri(string $seeder): string
    {
        return str_replace(':version', $this->version, $this->sde_repository) . $seeder::getSdeName() . '.csv';
    }

    /**
     * Determine the SDE local URI for the provided seeder.
     *
     * @param string $seeder
     * @return string
     */
    private function getSdeFileLocalUri(string $seeder): string
    {
        return storage_path('sde/' . $seeder::getSdeName() . '.csv');
    }
}
