<?php
/*
This file is part of SeAT

Copyright (C) 2015, 2016  Leon Jacobs

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

namespace Seat\Eveapi\Helpers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pheal\Cache\HashedNameFileStorage;
use Pheal\Core\Config;
use Pheal\Log\PsrLogger;
use Pheal\Pheal;
use Seat\Services\Settings\Seat;

/**
 * Class PhealSetup
 * @package Seat\Eveapi\Helpers
 */
class PhealSetup
{

    /**
     * @var
     */
    protected $instance;

    /**
     * @var
     */
    protected $logger;

    /**
     * Set the configuration parameters for Pheal
     */
    public function __construct()
    {

        // Just return the instance if we have already
        // configured it
        if ($this->instance !== null)
            return $this->instance;

        // Get a Pheal 'instance'
        $config = Config::getInstance();

        // Configure Pheal
        $config->access = new EveApiAccess;
        $config->cache = new HashedNameFileStorage(
            config('eveapi.config.pheal.cache_path'));
        $config->log = new PsrLogger(
            $this->getPhealLogger());
        $config->fetcher = app('Pheal\Fetcher\Guzzle');

        $config->api_customkeys = true;
        $config->http_timeout = 60;

        // Compile a user-agent string
        $config->http_user_agent = 'eveseat/' . config('eveapi.config.version') .
            ' ' . Seat::get('admin_contact');

        // Set the instance
        $this->instance = $config;

        return $this->instance;
    }

    /**
     * @return \Monolog\Logger
     */
    private function getPhealLogger()
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
     * @param null $key_id
     * @param null $v_code
     *
     * @return \Pheal\Pheal
     */
    public function getPheal($key_id = null, $v_code = null)
    {

        return new Pheal($key_id, $v_code);
    }

}
