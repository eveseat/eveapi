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

use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Services\EseyeClient;
use Seat\Services\AbstractSeatPlugin;
use Seat\Services\Contracts\EsiClient;
use Seat\Services\Contracts\EsiToken;

class EseyeServiceProvider extends AbstractSeatPlugin
{
    public function boot()
    {
        // Publish Eseye Configuration
        $this->publishEseyeConfig();
    }

    public function register()
    {
        // Register Eseye Configuration
        $this->registerEseyeConfig();

        // Bind Eseye Client
        $this->bindEseyeClient();

        // Bind Refresh Token
        $this->bindRefreshToken();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'SeAT Eseye';
    }

    /**
     * @inheritDoc
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/eveseat/eseye';
    }

    /**
     * @inheritDoc
     */
    public function getPackagistPackageName(): string
    {
        return 'eseye';
    }

    /**
     * @inheritDoc
     */
    public function getPackagistVendorName(): string
    {
        return 'eveseat';
    }

    /**
     * {@inheritdoc}
     */
    public function getChangelogUri(): ?string
    {
        return 'https://api.github.com/repos/eveseat/eseye/releases';
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
     * Publish Eseye configuration files - so user can tweak them.
     *
     * @return void
     */
    private function publishEseyeConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/eseye.php' => config_path('eseye.php'),
                __DIR__ . '/Config/eseye-cache.php' => config_path('eseye-cache.php'),
                __DIR__ . '/Config/eseye-logging.php' => config_path('eseye-logging.php'),
            ], ['config', 'eseye', 'seat']);
        }
    }

    /**
     * Register Eseye config in the stack.
     *
     * @return void
     */
    private function registerEseyeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/eveapi.scopes.php', 'eveapi.scopes');
        $this->mergeConfigFrom(__DIR__ . '/Config/eseye.php', 'eseye');
        $this->mergeConfigFrom(__DIR__ . '/Config/eseye-cache.php', 'cache.stores');
        $this->mergeConfigFrom(__DIR__ . '/Config/eseye-logging.php', 'logging.channels');
    }

    /**
     * Bind Eseye Client as Esi Client.
     *
     * @return void
     */
    private function bindEseyeClient(): void
    {
        $this->app->bind(EsiClient::class, EseyeClient::class);
    }

    /**
     * Bind Refresh Token as Esi Token.
     *
     * @return void
     */
    private function bindRefreshToken(): void
    {
        $this->app->bind(EsiToken::class, RefreshToken::class);
    }
}
