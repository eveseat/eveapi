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

namespace Seat\Eveapi\Services;

use Composer\InstalledVersions;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OutOfBoundsException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Seat\Eseye\Configuration;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;
use Seat\Eseye\Exceptions\InvalidAuthenticationException;
use Seat\Eseye\Exceptions\InvalidContainerDataException;
use Seat\Services\Contracts\EsiClient;
use Seat\Services\Contracts\EsiResponse;
use Seat\Services\Contracts\EsiToken;

class EseyeClient implements EsiClient
{
    private Eseye $instance;

    public function __construct()
    {
        try {
            $version = sprintf('v%s', InstalledVersions::getPrettyVersion('eveseat/eveapi'));
        } catch (OutOfBoundsException $e) {
            $version = 'dev';
        }

        $config = Configuration::getInstance();

        $config->http_client = Client::class;
        $config->http_stream_factory = HttpFactory::class;
        $config->http_request_factory = HttpFactory::class;
        $config->http_user_agent = sprintf('SeAT %s/%s', $version, setting('admin_contact', true));

        $config->logger = Log::channel('eseye');
        $config->cache = Cache::store('eseye');

        $config->esi_scheme = config('eseye.esi.service.scheme');
        $config->esi_host = config('eseye.esi.service.host');
        $config->esi_port = config('eseye.esi.service.port');
        $config->datasource = config('eseye.esi.service.datasource');
        $config->sso_scheme = config('eseye.sso.service.scheme');
        $config->sso_host = config('eseye.sso.service.host');
        $config->sso_port = config('eseye.sso.service.port');

        $this->instance = new Eseye();
    }

    /**
     * @return \Seat\Services\Contracts\EsiToken
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Seat\Eseye\Exceptions\InvalidAuthenticationException
     */
    public function getAuthentication(): EsiToken
    {
        $authentication = $this->instance->getAuthentication();

        $container = app()->make(EsiToken::class);
        $container->setAccessToken($authentication->access_token);
        $container->setRefreshToken($authentication->refresh_token);
        $container->setExpiresOn((new DateTime())->setTimestamp($authentication->token_expires));

        return $container;
    }

    /**
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function setAuthentication(EsiToken $authentication): EsiClient
    {
        $container = new EsiAuthentication([
            'client_id' => config('eseye.esi.auth.client_id'),
            'secret' => config('eseye.esi.auth.client_secret'),
            'access_token' => $authentication->getAccessToken(),
            'refresh_token' => $authentication->getRefreshToken(),
            'token_expires' => $authentication->getExpiresOn()->getTimestamp(),
            'scopes' => $authentication->getScopes(),
        ]);

        $this->instance->setAuthentication($container);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->instance->isAuthenticated();
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->instance->getVersion();
    }

    /**
     * @param  string  $version
     * @return \Seat\Services\Contracts\EsiClient
     */
    public function setVersion(string $version): EsiClient
    {
        $this->instance->setVersion($version);

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryString(): array
    {
        return $this->instance->getQueryString();
    }

    /**
     * @param  array  $query
     * @return \Seat\Services\Contracts\EsiClient
     */
    public function setQueryString(array $query): EsiClient
    {
        $this->instance->setQueryString($query);

        return  $this;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->instance->getBody();
    }

    /**
     * @param  array  $body
     * @return \Seat\Services\Contracts\EsiClient
     */
    public function setBody(array $body): EsiClient
    {
        $this->instance->setBody($body);

        return $this;
    }

    /**
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $uri_data
     * @return \Seat\Services\Contracts\EsiResponse
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Seat\Eseye\Exceptions\EsiScopeAccessDeniedException
     * @throws \Seat\Eseye\Exceptions\InvalidAuthenticationException
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     * @throws \Seat\Eseye\Exceptions\RequestFailedException
     * @throws \Seat\Eseye\Exceptions\UriDataMissingException
     */
    public function invoke(string $method, string $uri, array $uri_data = []): EsiResponse
    {
        $response = $this->instance->invoke($method, $uri, $uri_data);

        return new \Seat\Eveapi\Containers\EsiResponse(
            $response->raw,
            $response->raw_headers,
            $response->getErrorCode(),
            $response->isCachedLoad());
    }

    /**
     * @param  int  $page
     * @return \Seat\Services\Contracts\EsiClient
     */
    public function page(int $page): EsiClient
    {
        $this->instance->page($page);

        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function getLogger(): LoggerInterface
    {
        return $this->instance->getLogger();
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     *
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function getCache(): CacheInterface
    {
        return $this->instance->getConfiguration()->getCache();
    }

    /**
     * @throws InvalidAuthenticationException
     * @throws InvalidContainerDataException
     */
    public function getValidAccessToken(): string
    {
        return $this->instance->getValidAccessToken()->access_token;
    }
}
