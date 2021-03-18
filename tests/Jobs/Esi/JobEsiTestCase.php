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

namespace Seat\Eveapi\Tests\Jobs\Esi;

use Illuminate\Support\Facades\Event;
use Lunaweb\RedisMock\Providers\RedisMockServiceProvider;
use Orchestra\Testbench\TestCase;
use Seat\Eseye\Configuration;
use Seat\Eseye\Log\NullLogger;
use Seat\Eveapi\EveapiServiceProvider;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;

/**
 * Class JobEsiTestCase.
 * @package Seat\Eveapi\Tests\Jobs\Esi
 */
class JobEsiTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // redis hole
        $app['config']->set('database.redis.client', 'mock');

        // database setup
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // eseye setup
        $app['config']->set('esi.eseye_logfile', storage_path('logs'));
        $app['config']->set('esi.eseye_cache', storage_path('eseye'));
        $app['config']->set('esi.eseye_loglevel', 'debug');
        $app['config']->set('esi.eseye_esi_scheme', 'https');
        $app['config']->set('esi.eseye_esi_host', 'esi.evetech.net');
        $app['config']->set('esi.eseye_esi_port', 443);
        $app['config']->set('esi.eseye_esi_datasource', 'tranquility');
        $app['config']->set('esi.eseye_sso_scheme', 'https');
        $app['config']->set('esi.eseye_sso_host', 'login.eveonline.com');
        $app['config']->set('esi.eseye_sso_port', 443);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // ensure cache from previous tests will not infer with current tests.
        EsiInMemoryCache::clear();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        // override guzzle fetcher by using esi mock
        Configuration::getInstance()->fetcher = EsiMockFetcher::class;

        // override cache by using in-memory mock
        Configuration::getInstance()->cache = EsiInMemoryCache::class;

        // override logs by using honey pot
        Configuration::getInstance()->logger = NullLogger::class;

        $this->loadMigrationsFrom(realpath(__DIR__ . '/../../database/migrations'));
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            RedisMockServiceProvider::class,
            EveapiServiceProvider::class,
        ];
    }
}
