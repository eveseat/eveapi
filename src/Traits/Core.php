<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Leon Jacobs
Copyright (c) 2015 eveseat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace Seat\Eveapi\Traits;

use Pheal\Access\StaticCheck;
use Pheal\Cache\FileStorage;
use Pheal\Core\Config;
use Pheal\Pheal;

trait Core
{

    use Validation;

    protected $pheal = null;
    protected $key   = null;
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

        // TODO: Setup the identifying User-Agent
        Config::getInstance()->http_user_agent = 'Testing SeAT 1.0 (harro foxfour!)';

        return $this;
    }

    public function setKey($key, $vcode)
    {

        $this->validateKeyPair($key, $vcode);
        $this->key = $key;
        $this->vcode = $vcode;

        return;
    }

    public function getPheal()
    {

        $this->pheal = new Pheal($this->key, $this->vcode);

        return $this->pheal;

    }

}