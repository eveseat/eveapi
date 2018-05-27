<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterRole;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Traits\PerformsPreFlightChecking;
use Seat\Eveapi\Traits\RateLimitsEsiCalls;
use Seat\Services\Helpers\AnalyticsContainer;
use Seat\Services\Jobs\Analytics;

/**
 * Class EsiBase.
 * @package Seat\Eveapi\Jobs
 */
abstract class EsiBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        PerformsPreFlightChecking, RateLimitsEsiCalls;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

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
     * The SSO scope required to make the call.
     *
     * @var string
     */
    protected $scope = 'public';

    /**
     * The roles which are required in order to get access to an endpoint; in addition of a scope.
     *
     * @var array
     */
    protected $roles = [];

    /**
     * The page to retrieve.
     *
     * Jobs that expect paged responses should have
     * this value set.
     *
     * @var int
     */
    protected $page = null;

    /**
     * The body to send along with the request.
     *
     * @var array
     */
    protected $request_body = [];

    /**
     * Any query string parameters that should be sent
     * with the request.
     *
     * @var array
     */
    protected $query_string = [];

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    protected $token;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * @var bool
     */
    protected $public_call;

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
     * Get the current characters roles.
     *
     * @return array
     * @throws \Exception
     */
    public function getCharacterRoles(): array
    {

        return CharacterRole::where('character_id', $this->getCharacterId())
            // https://eve-seat.slack.com/archives/C0H3VGH4H/p1515081536000720
            // > @ccp_snowden: most things will require `roles`, most things are
            // > not contextually aware enough to make hq/base decisions
            ->where('scope', 'roles')
            ->pluck('role')->all();
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
            throw new Exception('No token specified');

        return $this->token->character_id;
    }

    /**
     * @param array $path_values
     *
     * @return \Seat\Eseye\Containers\EsiResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function retrieve(array $path_values = []): EsiResponse
    {

        $this->validateCall();

        $client = $this->eseye();
        $client->setVersion($this->version);
        $client->setBody($this->request_body);
        $client->setQueryString($this->query_string);

        // Configure the page to get
        if (! is_null($this->page))
            $client->page($this->page);

        // Generally, we want to bubble up exceptions all the way to the
        // callee. However, in the case of this worker class, we need to
        // try and be vigilant with tokens that may have expired. So for
        // those cases we wrap in a try/catch.
        try {

            $result = $client->invoke($this->method, $this->endpoint, $path_values);

        } catch (RequestFailedException $exception) {

            // If the token can't login and we get an HTTP 400 together with
            // and error message stating that this is an invalid_token, remove
            // the token from SeAT.
            if ($exception->getEsiResponse()->getErrorCode() == 400 &&
                $exception->getEsiResponse()->error() == 'invalid_token: The refresh token is expired.') {

                // Remove the invalid token
                $this->token->delete();
            }

            // Rethrow the exception
            throw $exception;
        }

        // If this is a cached load, don't bother with any further
        // processing.
        if ($result->isCachedLoad())
            return $result;

        // Perform error checking
        $this->logWarnings($result);

        // Update the refresh token we have stored in the database.
        $this->updateRefreshToken();

        return $result;
    }

    /**
     * Validates a call to ensure that a method and endpoint is set
     * in the job that is using this base class.
     *
     * @return void
     * @throws \Exception
     */
    public function validateCall(): void
    {

        if (! in_array($this->method, ['get', 'post', 'put', 'patch', 'delete']))
            throw new Exception('Invalid HTTP method used');

        if (trim($this->endpoint) === '')
            throw new Exception('Empty endpoint used');

        // Enfore a version specification unless this is a 'meta' call.
        if (trim($this->version) === '' && ! (in_array('meta', $this->tags())))
            throw new Exception('Version is empty');
    }

    /**
     * Assign this job a tag so that Horizon can categorize and allow
     * for specific tags to be monitored.
     *
     * If a job specifies the tags property, that is added to the
     * character_id tag that automatically gets appended.
     *
     * @return array
     * @throws \Exception
     */
    public function tags(): array
    {

        if (property_exists($this, 'tags')) {
            if (is_null($this->token))
                return array_merge($this->tags, ['public']);

            return array_merge($this->tags, ['character_id:' . $this->getCharacterId()]);
        }

        if (is_null($this->token))
            return ['unknown_tag', 'public'];

        return ['unknown_tag', 'character_id:' . $this->getCharacterId()];
    }

    /**
     * Get an instance of Eseye to use for this job.
     *
     * @return \Seat\Eseye\Eseye
     * @throws \Seat\Eseye\Exceptions\InvalidContainerDataException
     */
    public function eseye()
    {

        if ($this->client)
            return $this->client;

        $this->client = app('esi-client');

        if (is_null($this->token))
            return $this->client = $this->client->get();

        return $this->client = $this->client->get(new EsiAuthentication([
            'refresh_token' => $this->token->refresh_token,
            'access_token'  => $this->token->token,
            'token_expires' => $this->token->expires_on,
            'scopes'        => $this->token->scopes,
        ]));
    }

    /**
     * Logs warnings to the Eseye logger.
     *
     * These warnings will also cause analytics jobs to be
     * sent to allow for monitoring of endpoint changes.
     *
     * @param \Seat\Eseye\Containers\EsiResponse $response
     *
     * @throws \Throwable
     */
    public function logWarnings(EsiResponse $response): void
    {

        if (! is_null($response->pages) && $this->page === null) {

            $this->eseye()->getLogger()->warning('Response contained pages but none was expected');

            dispatch((new Analytics((new AnalyticsContainer)
                ->set('type', 'endpoint_warning')
                ->set('ec', 'unexpected_page')
                ->set('el', $this->version)
                ->set('ev', $this->endpoint))));
        }

        if (! is_null($this->page) && $response->pages === null) {

            $this->eseye()->getLogger()->warning('Expected a paged response but had none');

            dispatch((new Analytics((new AnalyticsContainer)
                ->set('type', 'endpoint_warning')
                ->set('ec', 'missing_pages')
                ->set('el', $this->version)
                ->set('ev', $this->endpoint))));
        }

        if (array_key_exists('Warning', $response->headers)) {

            $this->eseye()->getLogger()->warning('A response contained a warning: ' .
                $response->headers['Warning']);

            dispatch((new Analytics((new AnalyticsContainer)
                ->set('type', 'generic_warning')
                ->set('ec', 'missing_pages')
                ->set('el', $this->endpoint)
                ->set('ev', $response->headers['Warning']))));
        }
    }

    /**
     * Update the access_token last used in the job,
     * along with the expiry time.
     */
    public function updateRefreshToken(): void
    {

        tap($this->token, function ($token) {

            // If no API call was made, the client would never have
            // been instantiated and auth information never updated.
            if (is_null($this->client) || $this->public_call)
                return;

            $last_auth = $this->client->getAuthentication();

            $token->token = $last_auth->access_token ?? '-';
            $token->expires_on = $last_auth->token_expires;

            $token->save();
        });
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
     * When a job fails, grab some information and send a
     * GA event about the exception. The Analytics job
     * does the work of checking if analytics is disabled
     * or not, so we don't have to care about that here.
     *
     * On top of that, we also increment the error rate
     * limiter. This is checked as part of the preflight
     * checks when API calls are made.
     *
     * @param \Exception $exception
     *
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function failed(Exception $exception)
    {

        $this->incrementEsiRateLimit();

        // Analytics. Report only the Exception class and message.
        dispatch((new Analytics((new AnalyticsContainer)
            ->set('type', 'exception')
            ->set('exd', get_class($exception) . ':' . $exception->getMessage())
            ->set('exf', 1))));

        // Rethrow the original exception for Horizon
        throw $exception;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Get the corporation a refresh_token is associated with.
     *
     * This is based on the character's token we have corporation
     * membership.
     *
     * @return int
     * @throws \Exception
     */
    public function getCorporationId(): int
    {

        return CharacterInfo::where('character_id', $this->getCharacterId())
            ->first()->corporation_id;
    }
}
