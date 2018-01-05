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

namespace Seat\Eveapi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Containers\EsiResponse;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class EsiBase
 * @package Seat\Eveapi\Jobs
 */
abstract class EsiBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The HTTP method used for the API Call.
     *
     * Eg: GET, POST, PUT, DELETE
     *
     * @var string
     */
    protected $method = '';

    /**
     * The ESI endpoint to call.
     *
     * Eg: /characters/{character_id}/
     *
     * @var string
     */
    protected $endpoint = '';

    /**
     * The endpoint version to use.
     *
     * Eg: v1, v4
     *
     * @var int
     */
    protected $version = '';

    /**
     * The page to retreive.
     *
     * Jobs that expect paged responses should have
     * this value set.
     *
     * @var int
     */
    protected $page = null;

    /**
     * @var
     */
    private $token;

    /**
     * @var mixed
     */
    private $client;

    /**
     * @var bool
     */
    private $public_call;

    /**
     * Create a new job instance.
     *
     * If a null refresh token is provided, it is assumed that the
     * call that should be made is a public one.
     *
     * @param \Seat\Eveapi\Models\RefreshToken $token
     */
    public function __construct(RefreshToken $token = null)
    {

        if (is_null($token))
            $this->public_call = true;

        else
            $this->token = $token;
    }

    /**
     * Get the character_id we have for the token in this job.
     *
     * An exception will be thrown if an empty token is set.
     *
     * @return int
     * @throws \Exception
     */
    public function getCharacterId(): int
    {

        if (is_null($this->token))
            throw new \Exception('No token specified');

        return $this->token->character_id;
    }

    /**
     * @param array $path_values
     *
     * @return \Seat\Eseye\Containers\EsiResponse
     * @throws \Exception
     */
    public function retrieve(array $path_values = []): EsiResponse
    {

        $this->validateCall();

        $client = $this->eseye();
        $client->setVersion($this->version);

        // Configure the page to get
        if (! is_null($this->page))
            $client->page($this->page);

        $result = $client->invoke($this->method, $this->endpoint, $path_values);

        // Perform error checking
        $this->logWarnings($result);

        return $result;
    }

    /**
     * Validates a call to ensure that a method and endoint is set
     * in the job that is using this base class.
     *
     * @return void
     * @throws \Exception
     */
    public function validateCall(): void
    {

        if (! in_array($this->method, ['get', 'post', 'put', 'patch', 'delete']))
            throw new \Exception('Brokit method used');

        if (trim($this->endpoint) === '')
            throw new \Exception('Empty endpoint used');

        if (trim($this->version) === '')
            throw new \Exception('Version is empty');

    }

    /**
     * Get an instance of Eseye to use for this job.
     */
    public function eseye()
    {

        if ($this->client)
            return $this->client;

        $this->client = app('esi-client');

        if (is_null($this->token))
            return $this->client;

        return $this->client = $this->client->get(new EsiAuthentication([
            'refresh_token' => $this->token->refresh_token,
            'access_token'  => $this->token->token,
            'token_expires' => $this->token->expires_on,
            'scopes'        => $this->token->scopes,
        ]));
    }

    /**
     * @param \Seat\Eseye\Containers\EsiResponse $response
     *
     * @throws \Throwable
     */
    public function logWarnings(EsiResponse $response): void
    {

        // While development heavy, throw exceptions to help.
        throw_if(! is_null($response->pages) && $this->page === null,
            new \Exception('Response contained pages but none was expected'));

        if (array_key_exists('Warning', $response->headers)) {
            throw new \Exception('A response contained a warning: ' . $response->headers['Warning']);
        }

        // TODO: Log warnings such as:
        //
        //  An X-Pages header was received but no page was set.
        //  An endpoint was deprecated and is returning a Warning header.
    }

    /**
     * Check if there are any pages left in a response
     * based on the number of pages available and the
     * current page.
     *
     * @param int $pages
     *
     * @return bool
     */
    public function nextPage(int $pages): bool
    {

        if ($this->page >= $pages)
            return false;

        $this->page++;

        return true;
    }

    /**
     * Before we finish the job, update the access_token
     * last used in the job, along with the expiry time.
     */
    public function __destruct()
    {

        tap($this->token, function ($token) {

            $last_auth = $this->client->getAuthentication();

            $token->token = $last_auth->access_token ?? '-';
            $token->expires_on = $last_auth->token_expires;

            $token->save();
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle();

}
