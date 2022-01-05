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
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class SdeSeeder extends Seeder
{
    /**
     * @var string
     */
    private string $version = 'latest';

    public function run()
    {
        // extract sde file/seeder mapping from config
        $sde_mapping = [
            'mapDenormalize' => MapDenormalizeSeeder::class,
            'dgmTypeAttributes' => DgmTypeAttributesSeeder::class,
            'invControlTowerResources' => InvControlTowerResourcesSeeder::class,
            'invGroups' => InvGroupsSeeder::class,
            'invMarketGroups' => InvMarketGroupsSeeder::class,
            'invTypes' => InvTypesSeeder::class,
            'invTypeMaterials' => InvTypeMaterialsSeeder::class,
            'ramActivities' => RamActivitiesSeeder::class,
            'staStations' => StaStationsSeeder::class,
        ];

        if (! $this->isStorageOk())
            throw new DirectoryNotFoundException('Storage path is not OK. Please check permissions.');

        $this->command->info('Downloading static files...');
        $this->downloadStaticFiles(array_keys($sde_mapping));

        $this->command->info('Seeding SDE into Database...');
        $this->call(array_values($sde_mapping));
    }

    /**
     * Download the EVE Sde from Fuzzwork and save it
     * in the storage_path/sde folder.
     */
    private function downloadStaticFiles($files)
    {
        $bar = $this->getProgressBar(count($files));

        foreach ($files as $file) {

            $url = str_replace(':version', $this->version, 'https://www.fuzzwork.co.uk/dump/:version/') .
                $file . '.csv';
            $destination = storage_path('sde/' . $file . '.csv');

            $file_handler = fopen($destination, 'w');

            $result = Http::withOptions([
                'sink' => $file_handler,
            ])->get($url);

            fclose($file_handler);

            if ($result->status() != 200)
                $this->command->error('Unable to download: ' . $url .
                    '. The HTTP response was: ' . $result->status());

            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
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
        $this->command->line('SDE storage path is: "' . $storage . '"');

        if (File::isWritable(storage_path())) {

            // Check that the path exists
            if (! File::exists($storage))
                File::makeDirectory($storage, 0755, true);

            return true;
        }

        return false;
    }

    /**
     * Get a new progress bar to display based on the
     * amount of iterations we expect to use.
     *
     * @param $iterations
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    private function getProgressBar($iterations)
    {
        $bar = $this->command->getOutput()->createProgressBar($iterations);

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s%');

        return $bar;
    }
}
