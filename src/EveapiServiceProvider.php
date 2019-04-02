<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

namespace Seat\Eveapi;

use ApplicationInsights\Telemetry_Client;
use GuzzleHttp\Client;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Laravel\Horizon\Repositories\RedisMetricsRepository;
use Seat\Eveapi\Helpers\EseyeSetup;
use Seat\Services\AbstractSeatPlugin;

/**
 * Class EveapiServiceProvider.
 * @package Seat\Eveapi
 */
class EveapiServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // Inform Laravel how to load migrations
        $this->add_migrations();

        // Update api config
        $this->configure_api();

        // Jobs Telemetry
        Queue::before(function (JobProcessing $event) {

            // retrieve server IP or init to localhost
            $server_ip = array_key_exists('SERVER_ADDR', $_SERVER) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';

            // push a Telemetry Client into the Job
            $event->job->telemetryClient = new Telemetry_Client();
            $event->job->telemetryClient->getContext()->setInstrumentationKey(env('AZURE_APP_INSIGHT_KEY'));
            $event->job->telemetryClient->getContext()->getLocationContext()->setIp($server_ip);
            $event->job->telemetryClient->getChannel()->setClient(new Client());

            // init the average time when the job start
            $event->job->startTime = microtime(true);
        });

        Queue::after(function (JobProcessed $event) {

            // if the job doesn't have any Telemetry Client, exit
            if (! property_exists($event->job, 'telemetryClient')) {
                logger()->debug(
                    sprintf('No telemetry client is available for the job %s', $event->job->getName()));

                return;
            }

            // use Horizon stats to retrieve job metric
            $stats = new RedisMetricsRepository($this->app->get('redis'));

            // build metadata
            $job_name = $event->job->payload()['displayName'];
            $job_class = $event->job->payload()['data']['commandName'];
            $job_duration = $stats->runtimeForJob($job_class);

            // if the job doesn't have any start time set, init it to now
            if (! property_exists($event->job, 'startTime'))
                $event->job->startTime = microtime(true);

            // log and send the telemetry
            $event->job->telemetryClient->trackDependency(
                $job_name, 'ESI', 'handle', $event->job->startTime, $job_duration);

            $event->job->telemetryClient->flush();
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.config.php', 'eveapi.config');
        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.scopes.php', 'eveapi.scopes');

        // Eseye Singleton
        $this->app->singleton('esi-client', function () {

            return new EseyeSetup;
        });
    }

    /**
     * Set the path for migrations which should
     * be migrated by laravel. More informations:
     * https://laravel.com/docs/5.5/packages#migrations.
     */
    private function add_migrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Update Laravel 5 Swagger annotation path.
     */
    private function configure_api()
    {
        // ensure current annotations setting is an array of path or transform into it
        $current_annotations = config('l5-swagger.paths.annotations');
        if (! is_array($current_annotations))
            $current_annotations = [$current_annotations];

        // merge paths together and update config
        config([
            'l5-swagger.paths.annotations' => array_unique(array_merge($current_annotations, [
                __DIR__ . '/Models',
            ])),
        ]);
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'SeAT Eve API';
    }

    /**
     * Return the plugin repository address.
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/eveseat/eveapi';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     */
    public function getPackagistPackageName(): string
    {
        return 'eveapi';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     */
    public function getPackagistVendorName(): string
    {
        return 'eveseat';
    }

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return config('eveapi.config.version');
    }
}
