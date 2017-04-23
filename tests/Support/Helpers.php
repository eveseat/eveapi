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

namespace Seat\Eveapi\Test\Support;

use Pheal\Cache\NullStorage;
use Pheal\Core\Config;
use Pheal\Log\NullStorage as NullLogStorage;
use Pheal\Pheal;

/**
 * Class Helpers
 * @package Seat\Eveapi\Test\Support
 */
class Helpers
{

    /**
     * @return \Pheal\Pheal
     */
    public static function setup_pheal()
    {

        $config = Config::getInstance();

        // Configure Pheal
        $config->cache = new NullStorage();
        $config->log = new NullLogStorage();
        $config->api_customkeys = true;
        $config->http_method = 'curl';
        $config->http_timeout = 60;
        $config->http_ssl_verifypeer = false;
        $config->http_ssl_certificate_file = 'cacert.pem';

        // TODO: Setup the identifying User-Agent
        $config->http_user_agent = 'SeAT Test Suite Client';

        return new Pheal();

    }
}
