<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

use Seat\Eveapi\Helpers\EseyeSetup;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Observers\CharacterAffiliationObserver;
use Seat\Eveapi\Observers\RefreshTokenObserver;
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

        // Register ESI configuration
        $this->add_esi_config();

        // Update api config
        $this->configure_api();

        // Register events observers
        $this->add_events();
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
     * Register the custom events that may fore for this package.
     */
    private function add_events()
    {
        CharacterAffiliation::observe(CharacterAffiliationObserver::class);
        RefreshToken::observe(RefreshTokenObserver::class);
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
