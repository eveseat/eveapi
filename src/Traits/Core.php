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

namespace Seat\Eveapi\Traits;

use Pheal\Access\StaticCheck;
use Pheal\Cache\FileStorage;
use Pheal\Core\Config;
use Pheal\Pheal;

/**
 * Class Core
 * @package Seat\Eveapi\Traits
 */
trait Core
{

    use Validation;

    /**
     * @var null
     */
    protected $pheal = null;

    /**
     * @var null
     */
    protected $key = null;

    /**
     * @var null
     */
    protected $vcode = null;

    /**
     * @return $this
     */
    public function start()
    {

        // Configure Pheal
        Config::getInstance()->cache = new FileStorage(storage_path() . '/app/pheal/');
        Config::getInstance()->access = new StaticCheck();
        Config::getInstance()->log = new \Pheal\Log\FileStorage(storage_path() . '/logs/');
        Config::getInstance()->api_customkeys = true;
        Config::getInstance()->http_method = 'curl';
        Config::getInstance()->http_timeout = 60;

        // TODO: Setup the identifying User-Agent
        Config::getInstance()->http_user_agent = 'Testing SeAT 1.0 (harro foxfour!)';

        return $this;
    }

    /**
     * @param $key
     * @param $vcode
     *
     * @return $this
     * @throws \Seat\Eveapi\Exception\InvalidKeyPairException
     */
    public function setKey($key, $vcode)
    {

        $this->validateKeyPair($key, $vcode);
        $this->key = $key;
        $this->vcode = $vcode;

        return $this;
    }

    /**
     * @return null|\Pheal\Pheal
     */
    public function getPheal()
    {

        $this->pheal = new Pheal(
            $this->key, $this->vcode);

        return $this->pheal;

    }

}