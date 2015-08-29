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

namespace Seat\Eveapi\Api\Eve;

use Pheal\Exceptions\PhealException;
use Seat\Eveapi\Models\EveErrorList;
use Seat\Eveapi\Traits\Boot;
use Seat\Eveapi\Traits\Cleanup;
use Seat\Eveapi\Traits\Core;

/**
 * Class RefTypes
 * @package Seat\Eveapi\Server
 */
class ErrorList
{

    use Boot, Core, Cleanup;

    /**
     * Run the Ref Types Update
     */
    public function call()
    {

        $result = $this->getPheal()
            ->eveScope
            ->ErrorList();

        foreach ($result->errors as $error) {

            EveErrorList::firstOrCreate(
                [
                    'errorCode' => $error->errorCode,
                    'errorText' => $error->errorText
                ]);
        }

        return;
    }

}