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

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\Research as ResearchModel;

/**
 * Class Research
 * @package Seat\Eveapi\Api\Character
 */
class Research extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->Research([
                'characterID' => $character->characterID]);

            foreach ($result->research as $research_agent) {

                $research_info = ResearchModel::firstOrNew([
                    'characterID' => $character->characterID,
                    'agentID'     => $research_agent->agentID]);

                $research_info->fill([
                    'skillTypeID'       => $research_agent->skillTypeID,
                    'researchStartDate' => $research_agent->researchStartDate,
                    'pointsPerDay'      => $research_agent->pointsPerDay,
                    'remainderPoints'   => $research_agent->remainderPoints
                ]);

                $research_info->save();
            }
        }

        return;
    }
}
