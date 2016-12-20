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

namespace Seat\Eveapi\Api\Corporation;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Corporation\Medal;

/**
 * Class Medals
 * @package Seat\Eveapi\Api\Corporation
 */
class Medals extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('corp')
            ->setCorporationID()->getPheal();

        $result = $pheal->Medals();

        $this->writeJobLog('medals',
            'API responded with ' . count($result->medals) . ' medals');

        foreach ($result->medals as $medal) {

            $medal_info = Medal::firstOrNew([
                'corporationID' => $this->corporationID,
                'medalID'       => $medal->medalID]);

            $medal_info->fill([
                'title'       => $medal->title,
                'description' => $medal->description,
                'creatorID'   => $medal->creatorID,
                'created'     => $medal->created
            ]);

            $medal_info->save();
        }

        return;
    }
}
