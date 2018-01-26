<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

use Illuminate\Support\ServiceProvider;
use Seat\Eveapi\Console\EsiJobMakeCommand;
use Seat\Eveapi\Helpers\EseyeSetup;

/**
 * Class EveapiServiceProvider.
 * @package Seat\Eveapi
 */
class EveapiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // Add builder commands
        if ($this->app->runningInConsole())
            $this->commands([
                EsiJobMakeCommand::class,
            ]);

        // Publish migrations
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
        ]);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.config.php', 'eveapi.config');
        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.corp_roles.php', 'eveapi.corp_roles');
        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.scopes.php', 'eveapi.scopes');

        // Eseye Singleton
        $this->app->singleton('esi-client', function () {

            return new EseyeSetup;
        });
    }
}
