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

namespace Seat\Eveapi;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Seat\Eveapi\Commands\Esi\Job\Dispatch;
use Seat\Eveapi\Commands\Esi\Meta\Ping;
use Seat\Eveapi\Commands\Esi\Update\Affiliations;
use Seat\Eveapi\Commands\Esi\Update\Alliances;
use Seat\Eveapi\Commands\Esi\Update\Characters;
use Seat\Eveapi\Commands\Esi\Update\Contracts;
use Seat\Eveapi\Commands\Esi\Update\Corporations;
use Seat\Eveapi\Commands\Esi\Update\Insurances;
use Seat\Eveapi\Commands\Esi\Update\Killmails;
use Seat\Eveapi\Commands\Esi\Update\Notifications;
use Seat\Eveapi\Commands\Esi\Update\Prices;
use Seat\Eveapi\Commands\Esi\Update\PublicInfo;
use Seat\Eveapi\Commands\Esi\Update\Sovereignty;
use Seat\Eveapi\Commands\Esi\Update\Stations;
use Seat\Eveapi\Commands\Esi\Update\Status as EsiStatus;
use Seat\Eveapi\Commands\Eve\Update\Sde;
use Seat\Eveapi\Commands\Eve\Update\Status as EveStatus;
use Seat\Eveapi\Commands\Make\Job\Esi;
use Seat\Eveapi\Commands\Seat\Admin\Diagnose;
use Seat\Eveapi\Commands\Seat\Admin\Maintenance;
use Seat\Eveapi\Commands\Seat\Buckets\Balance;
use Seat\Eveapi\Commands\Seat\Buckets\Info;
use Seat\Eveapi\Commands\Seat\Buckets\ListCommand;
use Seat\Eveapi\Commands\Seat\Buckets\Update;
use Seat\Eveapi\Commands\Seat\Cache\Clear;
use Seat\Eveapi\Commands\Seat\Tokens\Upgrade;
use Seat\Eveapi\Helpers\EseyeSetup;
use Seat\Eveapi\Listeners\EsiFailedCall;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Observers\CharacterAffiliationObserver;
use Seat\Eveapi\Observers\RefreshTokenObserver;
use Seat\Services\AbstractSeatPlugin;

/**
 * Class EveapiServiceProvider.
 *
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
        // Register commands
        $this->addCommands();

        // Inform Laravel how to load migrations
        $this->add_migrations();

        // Register ESI configuration
        $this->add_esi_config();

        // Update api config
        $this->configure_api();

        // Register events observers
        $this->addObservers();

        // Register events listeners
        $this->addListeners();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.scopes.php', 'eveapi.scopes');

        // Eseye Singleton
        $this->app->singleton('esi-client', function () {

            return new EseyeSetup;
        });
    }

    private function addCommands()
    {
        $this->commands([
            // Buckets
            Info::class,
            Balance::class,
            Update::class,
            ListCommand::class,

            // SeAT
            Clear::class,
            Diagnose::class,
            Maintenance::class,

            // Dev
            Esi::class,

            // Sde
            Sde::class,

            // Esi
            Ping::class,
            Characters::class,
            Corporations::class,
            Notifications::class,
            PublicInfo::class,
            Affiliations::class,
            Prices::class,
            Insurances::class,
            Stations::class,
            Sovereignty::class,
            Alliances::class,
            Contracts::class,
            Killmails::class,
            Dispatch::class,
            EsiStatus::class,
            EveStatus::class,

            // SSO
            Upgrade::class,
        ]);
    }

    /**
     * Set the path for migrations which should
     * be migrated by laravel. More information:
     * https://laravel.com/docs/5.5/packages#migrations.
     */
    private function add_migrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Publish esi configuration file - so user can tweak it.
     */
    private function add_esi_config()
    {
        $this->publishes([
            __DIR__ . '/Config/esi.php' => config_path('esi.php'),
        ], ['config', 'seat']);
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
     * Register the custom model observers.
     */
    private function addObservers()
    {
        CharacterAffiliation::observe(CharacterAffiliationObserver::class);
        RefreshToken::observe(RefreshTokenObserver::class);
    }

    /**
     * Register the custom event listeners.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function addListeners()
    {
        $events = $this->app->make(Dispatcher::class);

        $events->listen(JobExceptionOccurred::class, EsiFailedCall::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangelogUri(): ?string
    {
        return 'https://api.github.com/repos/eveseat/eveapi/releases';
    }

    /**
     * {@inheritdoc}
     */
    public function getChangelogBodyAttribute(): ?string
    {
        return 'body';
    }

    /**
     * {@inheritdoc}
     */
    public function getChangelogTagAttribute(): ?string
    {
        return 'tag_name';
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
}
