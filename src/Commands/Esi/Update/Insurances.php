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

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Fittings\Insurances as InsurancesJob;

/**
 * Class Insurances.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Insurances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:insurances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule updater jobs for insurances information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        InsurancesJob::dispatch();

        $this->info('A new insurance job has been queued.');

        return $this::SUCCESS;
    }
}
