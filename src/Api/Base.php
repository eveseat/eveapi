<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Api;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pheal\Cache\HashedNameFileStorage;
use Pheal\Core\Config;
use Pheal\Log\PsrLogger;
use Pheal\Pheal;
use Pheal\RateLimiter\FileLockRateLimiter;
use Seat\Eveapi\Exception\InvalidScopeException;
use Seat\Eveapi\Helpers\EveApiAccess;
use Seat\Eveapi\Models\EveApiKey;
use Seat\Eveapi\Traits\Validation;

/**
 * This abstract contains the call contract that
 * needs to be used by all update workers making
 * use of this classes concrete functions.
 *
 * Class Base
 * @package Seat\Eveapi\Api
 */
abstract class Base
{

    use Validation;

    /**
     * @var null
     */
    protected $pheal = null;

    /**
     * @var null
     */
    protected $api_info = null;

    /**
     * @var null
     */
    protected $key_id = null;

    /**
     * @var null
     */
    protected $v_code = null;

    /**
     * @var null
     */
    protected $scope = null;

    /**
     * @var null
     */
    protected $corporationID = null;

    /**
     * @var null
     */
    protected $logger = null;

    /**
     * The contract for the update call. All
     * update should at least have this function
     *
     * @return mixed
     */
    abstract protected function call();

    /**
     * Setup the updater instance
     */
    public function __construct()
    {

        $this->setup();

    }

    /**
     * Configure a Psr-Style logger to be given
     * to PhealNG for logging requests. This logger
     * will rotate logs within a timespan of 30 days.
     *
     * @return \Monolog\Logger|null
     */
    private function getLogger()
    {

        // If its already setup, just return it.
        if (!is_null($this->logger))
            return $this->logger;

        // Configure the logger by setting the logfile
        // path and the format logs should be.
        $log_file = storage_path('logs/pheal.log');
        $format = new LineFormatter(null, null, false, true);

        $stream = new RotatingFileHandler($log_file, 30, Logger::INFO);
        $stream->setFormatter($format);

        $this->logger = new Logger('pheal');
        $this->logger->pushHandler($stream);

        return $this->logger;

    }

    /**
     * Configure PhealNG for use.
     *
     * @return $this
     */
    public function setup()
    {

        $config = Config::getInstance();

        // Configure Pheal
        $config->cache = new HashedNameFileStorage(storage_path() . '/app/pheal/');
        $config->access = new EveApiAccess(config('eveapi.access_bits'));
        $config->log = new PsrLogger($this->getLogger());
        $config->rateLimiter = new FileLockRateLimiter(storage_path() . '/app/pheal/');
        $config->api_customkeys = true;
        $config->http_method = 'curl';
        $config->http_timeout = 60;

        // TODO: Setup the identifying User-Agent
        $config->http_user_agent = 'Testing SeAT 1.0 (harro foxfour!)';

        return $this;
    }

    /**
     * Sets the API credentials to use with API requests.
     *
     * @param \Seat\Eveapi\Models\EveApiKey $api_info
     *
     * @return $this
     * @throws \Seat\Eveapi\Exception\InvalidKeyPairException
     * @throws \Seat\Eveapi\Exception\MissingKeyPairException
     *
     */
    public function setApi(EveApiKey $api_info)
    {

        $this->validateKeyPair(
            $api_info->key_id,
            $api_info->v_code
        );

        // Set the key_id & v_code properties
        $this->key_id = $api_info->key_id;
        $this->v_code = $api_info->v_code;

        // Set the EveApiKey Object
        $this->api_info = $api_info;

        return $this;
    }

    /**
     * Configure the scope for which API calls will
     * be made
     *
     * @param $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {

        $this->validateScope($scope);
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get a PhealNG instance. This method will prepare
     * the authentication details based on the properties
     * and return a ready to use Object
     *
     * @return null|\Pheal\Pheal
     */
    public function getPheal()
    {

        // Setup the Pheal instance with the key
        $this->pheal = new Pheal(
            $this->key_id,
            $this->v_code
        );

        // Give Pheal the key type and accessMask
        // information if we have it. This will be
        // used by the access checking logic.
        if ($this->api_info)
            if ($this->api_info->info)
                $this->pheal->setAccess(
                    $this->api_info->info->type,
                    $this->api_info->info->accessMask);

        // Check if a scope was set.
        if (!is_null($this->scope))
            $this->pheal->scope = $this->scope;

        return $this->pheal;

    }

    /**
     * Sets the corporationID to use in Corporation
     * related API update work
     *
     * @return $this
     * @throws \Seat\Eveapi\Exception\InvalidScopeException
     */
    public function setCorporationID()
    {

        if ($this->scope != 'corp')
            throw new InvalidScopeException(
                'This method only supports calls to the corp scope.');

        $this->corporationID = $this->api_info
            ->characters()->first()->corporationID;

        return $this;
    }

    /**
     * Cleanup actions.
     */
    public function __destruct()
    {

        $this->pheal = null;
        $this->api_info = null;
        $this->scope = null;
        $this->logger = null;

        return;
    }

}
