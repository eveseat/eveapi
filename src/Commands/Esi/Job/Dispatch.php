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

namespace Seat\Eveapi\Commands\Esi\Job;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Jobs\AbstractCharacterJob;
use Seat\Eveapi\Jobs\AbstractCorporationJob;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Dispatch.
 *
 * @package Seat\Eveapi\Commands\Esi\Job
 */
class Dispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:job:dispatch {job_class} {--character_id=} {--corporation_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches an ESI update job class for a specific character id.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $class = $this->argument('job_class');

        if (! class_exists($class)) {
            $this->error('Invalid job name - the class does not exist!');

            return $this::INVALID;
        }

        switch (true) {
            case is_subclass_of($class, AbstractAuthCharacterJob::class):
                return $this->handlePrivateCharacterJobs($class);
            case is_subclass_of($class, AbstractAuthCorporationJob::class):
                return $this->handlePrivateCorporationJobs($class);
            case is_subclass_of($class, AbstractCharacterJob::class):
                return $this->handlePublicCharacterJobs($class);
            case is_subclass_of($class, AbstractCorporationJob::class):
                return $this->handlePublicCorporationJobs($class);
            default:
                return $this->handleGenericJobs($class);
        }
    }

    private function handlePublicCharacterJobs(string $job): int
    {
        if (! $this->option('character_id')) {
            $this->error('Missing mandatory character_id for an authenticated character job!');

            return $this::INVALID;
        }

        $job::dispatch($this->option('character_id'));

        $this->info('Job dispatched!');

        return $this::SUCCESS;
    }

    private function handlePrivateCharacterJobs(string $job): int
    {
        if (! $this->option('character_id')) {
            $this->error('Missing mandatory character_id for an authenticated character job!');

            return $this::INVALID;
        }

        $refresh_token = RefreshToken::findOrFail($this->option('character_id'));
        $job::dispatch($refresh_token);

        $this->info('Job dispatched!');

        return $this::SUCCESS;
    }

    private function handlePublicCorporationJobs(string $job): int
    {
        if (! $this->option('corporation_id')) {
            $this->error('Missing mandatory corporation_id for an authenticated corporation job!');

            return $this::INVALID;
        }

        $job::dispatch($this->option('corporation_id'));

        $this->info('Job dispatched!');

        return $this::SUCCESS;
    }

    private function handlePrivateCorporationJobs(string $job): int
    {
        if (! $this->option('corporation_id')) {
            $this->error('Missing mandatory corporation_id for an authenticated corporation job!');

            return $this::INVALID;
        }

        if (! $this->option('character_id')) {
            $this->error('Missing mandatory character_id for an authenticated corporation job!');

            return $this::FAILURE;
        }

        $refresh_token = RefreshToken::findOrFail($this->option('character_id'));
        $job::dispatch($this->option('corporation_id'), $refresh_token);

        $this->info('Job dispatched!');

        return $this::SUCCESS;
    }

    private function handleGenericJobs(string $job): int
    {
        if (! is_subclass_of($job, EsiBase::class))
            $this->warn('The job is not part of Esi stack.');

        $job::dispatch();

        $this->info('Job dispatched!');

        return $this::SUCCESS;
    }
}
