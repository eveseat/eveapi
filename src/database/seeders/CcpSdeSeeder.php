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

namespace Seat\Eveapi\Database\Seeders;

use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\ChrFactionsSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\DgmTypeAttributesSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvTypesSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\DgmTypeEffectsSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvCategoriesSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvContrabandTypesSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvControlTowerResourcesSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvGroupsSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvMarketGroupsSeeder;
use Seat\Eveapi\Database\Seeders\Sde\AbstractSdeSeeder;
use Seat\Eveapi\Database\Seeders\Sde\Ccp\InvMetaGroupsSeeder;
use Seat\Services\Settings\Seat;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * SdeSeeder.
 *
 * Used as a facade to seed all SDE related tables.
 */
class CcpSdeSeeder extends Seeder
{

    /**
     * @var string
     */
    private $version = '3720952';

    /**
     * The SDE file storage path.
     *
     * @var
     */
    protected $storage_path;

    private $imported_files = [];

    // TODO, would be nice to dynamically build this...
    private const  SEEDER_MAP = [
        'types.jsonl' => InvTypesSeeder::class,
        'typeDogma.jsonl' => [DgmTypeAttributesSeeder::class, DgmTypeEffectsSeeder::class],
        'factions.jsonl' => ChrFactionsSeeder::class,
        'categories.jsonl' => InvCategoriesSeeder::class,
        'contrabandTypes.jsonl' => InvContrabandTypesSeeder::class,
        'controlTowerResources.jsonl' => InvControlTowerResourcesSeeder::class,
        'groups.jsonl' => InvGroupsSeeder::class,
        'marketGroups.jsonl' => InvMarketGroupsSeeder::class,
        'metaGroups.jsonl' => InvMetaGroupsSeeder::class,
    ];

    private $seeders = [];

    /**
     * @throws \Seat\Eveapi\Exception\InvalidSdeSeederException
     */
    public function run()
    {
        // extract sde file/seeder mapping from config
        // $sde_seeders = config('seat.sde.seeders', []);
        // configure sde version
        $this->version = $this->getVersion();
        $this->command->info('Checking configuration...');
        if (! $this->isStorageOk())
            throw new DirectoryNotFoundException('Storage path is not OK. Please check permissions.');

        $this->command->info('Using SDE Version: ' . $this->version);

        $this->command->info('Downloading static files...');
        $this->downloadStaticFiles();

        $keeps_backup = DB::connection()->getDriverName() === 'mysql';

        if ($keeps_backup) {
            $this->command->info('Each table is copied before it is replaced. If one fails to load, its copy is put back');
            $this->command->info('automatically, so SeAT keeps the data it had and there is nothing to fix by hand.');
        }

        $this->command->info('Seeding SDE into Database...');

        try {
            $this->call($this->seeders);
        } catch (\Throwable $e) {
            $this->command->newLine();
            $this->command->error('The CCP SDE update stopped: ' . self::reason($e));

            if ($keeps_backup) {
                $done = array_keys(AbstractSdeSeeder::pullResults());
                $this->command->warn('The table being loaded was put back as it was.');
                $this->command->warn($done
                    ? 'Already updated to build ' . $this->version . ': ' . implode(', ', $done) . '. Every other table is unchanged.'
                    : 'No table was changed.');
                $this->command->warn('Once the cause is fixed, run the CCP seeder again.');
            }

            throw $e;
        }

        [$build, $released] = $this->recordInstalledVersion();

        $this->reportResults($build, $released);
    }

    /**
     * A failed insert's message carries the whole batch it was running, hundreds of
     * rows of type descriptions. The driver's own message says what went wrong.
     *
     * @param  \Throwable  $e
     * @return string
     */
    public static function reason(\Throwable $e): string
    {
        if ($e instanceof QueryException && $e->getPrevious())
            return $e->getPrevious()->getMessage();

        return $e->getMessage();
    }

    /**
     * @param  string  $build
     * @param  string|null  $released
     * @return void
     */
    private function reportResults(string $build, ?string $released): void
    {
        $format = fn ($count) => is_null($count) ? '-' : number_format($count);

        $results = collect(AbstractSdeSeeder::pullResults());

        $rows = $results
            ->map(fn ($result, $table) => [
                $table,
                $format($result['before']),
                $format($result['after']),
                $format($result['added']),
                $format($result['removed']),
            ])
            ->values()
            ->all();

        $this->command->newLine();
        $this->command->table(['Table', 'Rows before', 'Rows after', 'Added', 'Removed'], $rows);

        $this->command->info(sprintf('CCP SDE build %s%s is installed. All %d tables were updated.',
            $build, $released ? ' (released ' . $released . ')' : '', count($rows)));

        // A row count from before is only known where a copy was taken.
        if ($results->contains(fn ($result) => ! is_null($result['before'])))
            $this->command->info('Every table loaded, so the copies taken before the update have been deleted.');
    }

    /**
     * A seed that was killed part way (out of memory, container stopped) leaves that
     * table's backup behind and the table half loaded until the seeder runs again.
     *
     * @return bool
     */
    public static function hasInterruptedRun(): bool
    {
        return collect(self::SEEDER_MAP)->flatten()
            ->contains(fn ($seeder) => Schema::hasTable($seeder::getBackupName()));
    }

    /**
     * The CCP build this seeder installs.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return (string) (config('eveapi.config.sde.version') ?? $this->version);
    }

    /**
     * Kept apart from installed_sde: eve:update:sde compares that one with the
     * Fuzzwork release on every boot and would reimport over these tables if it changed.
     *
     * @return array build number, and CCP's release date when the build says it
     */
    private function recordInstalledVersion(): array
    {
        $build = $this->version;
        $released = null;

        $meta = $this->storage_path . '_sde.jsonl';
        if (file_exists($meta)) {
            $sde = json_decode((string) strtok(file_get_contents($meta), "\n"), true) ?: [];

            if (isset($sde['buildNumber']))
                $build = (string) $sde['buildNumber'];

            if (isset($sde['releaseDate']))
                $released = substr($sde['releaseDate'], 0, 10);
        }

        Seat::set('installed_ccp_sde', $released ? sprintf('%s (%s)', $build, $released) : $build);

        return [$build, $released];
    }

    
    /**
     * Download the EVE Sde from Fuzzwork and save it
     * in the storage_path/sde folder.
     */
    private function downloadStaticFiles()
    {
        $sde = sprintf('eve-online-static-data-%d-jsonl.zip', $this->version);

        $url = sprintf('https://developers.eveonline.com/static-data/tranquility/%s', $sde);
        $destination = $this->storage_path . $sde;

        // Now actually start fetching it!
        $result = Http::sink($destination)->get($url);
        // Check we are actually setting UA.
        // dump($result->transferStats->getRequest()->getHeaders());
        $result->throw();

        // Now need to extract the zip file.
        $this->extractZipWithProgress($destination, $this->storage_path);
        if (file_exists($destination)) {
            unlink($destination);
            $this->command->info("Deleted ZIP file: $destination");
        }
    }

    private function extractZipWithProgress(string $zipPath, string $destination): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->command->error("Could not open ZIP file: $zipPath");
            return;
        }

        $fileCount = $zip->numFiles;

        $this->command->info("Extracting ZIP ($fileCount files)…");
        $progressBar = $this->getProgressBar($fileCount);
        $progressBar->start();

        // Loop through files and extract individually
        for ($i = 0; $i < $fileCount; $i++) {
            $fileInfo = $zip->statIndex($i);
            $fileName = $fileInfo['name'];
            $this->imported_files[] = $fileName;

            // Extract single file
            $zip->extractTo($destination, $fileName);

            $seeder = $this->resolveSeeder($fileName);
            if (! is_null($seeder)) {
                $this->seeders = array_merge($this->seeders, $seeder);
                // $this->command->info("Adding seeder " . $fileName . " In location: " . $destination);
            }

            // Advance progress bar
            $progressBar->advance();
        }

        $zip->close();

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Extraction completed: $destination");
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

            $this->storage_path = $storage;

            return true;
        }

        return false;
    }

    private function resolveSeeder(string $filename): ?array
    {
        if (array_key_exists(basename($filename), self::SEEDER_MAP)) {
            $seeder =  self::SEEDER_MAP[basename($filename)];
            if (is_array($seeder)) 
                return $seeder;
            return [$seeder];
        }

        return null;
    }
}
