<?php

namespace Seat\Eveapi\Tests\Init;

use Orchestra\Testbench\TestCase;

abstract class AbstractBaseTest extends TestCase
{
    /**
     * 
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:fresh', [
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__ . '/../../src/database/migrations'),
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Seat\Eveapi\EveapiServiceProvider::class,
            \Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }
}
