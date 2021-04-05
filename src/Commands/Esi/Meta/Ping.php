<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Commands\Esi\Meta;

use Illuminate\Console\Command;
use Seat\Eseye\Cache\NullCache;
use Seat\Eseye\Configuration;
use Seat\Eseye\Exceptions\RequestFailedException;

/**
 * Class Ping.
 *
 * @package Seat\Eveapi\Commands\Esi\Meta
 */
class Ping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:meta:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform an ESI status check';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException|\Illuminate\Container\EntryNotFoundException
     */
    public function handle()
    {

        $esi = app('esi-client')->get();
        $esi->setVersion('');   // meta URI lives in /
        Configuration::getInstance()->cache = NullCache::class;

        try {

            $esi->invoke('get', '/ping');

        } catch (RequestFailedException $e) {

            $this->error('ESI does not appear to be available: ' . $e->getMessage());
        }

        $this->info('ESI appears to be OK');
    }
}
