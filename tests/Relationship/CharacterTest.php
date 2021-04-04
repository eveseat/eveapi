<?php
/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Tests\Relationship;


use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Character\CharacterAgentResearch;
use Seat\Eveapi\Models\Character\CharacterBlueprint;
use Seat\Eveapi\Models\Character\CharacterCorporationHistory;
use Seat\Eveapi\Models\Character\CharacterFatigue;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterInfoSkill;
use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Eveapi\Models\Location\CharacterLocation;
use Seat\Eveapi\Models\Location\CharacterOnline;
use Seat\Eveapi\Models\Location\CharacterShip;
use Seat\Eveapi\Models\Wallet\CharacterWalletBalance;
use Seat\Eveapi\Tests\BaseTestCase;

/**
 * Class CharacterTest.
 * @package Seat\Eveapi\Tests\Relationship
 */
class CharacterTest extends BaseTestCase
{
    private $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->character = CharacterInfo::create([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);
    }

    public function testHasAffiliation()
    {
        $affiliation = new CharacterAffiliation([
            'corporation_id' => 109299958,
        ]);

        $this->character->affiliation()->save($affiliation);

        $this->assertEquals($affiliation->corporation_id, $this->character->affiliation->corporation_id);
    }

    public function testHasDefaultAffiliation()
    {
        $this->assertNotNull($this->character->affiliation);
    }

    public function testHasAgentResearches()
    {
        $this->character->agent_research()->save(new CharacterAgentResearch([
            'agent_id'=> 3014221,
            'points_per_day'=> 31.25,
            'remainder_points'=> 76877.06,
            'skill_type_id'=> 11445,
            'started_at'=> '2016-01-10T00:39:55Z',
        ]));

        $this->character->agent_research()->save(new CharacterAgentResearch([
            'agent_id'=> 3012880,
            'points_per_day'=> 21.93,
            'remainder_points'=> -12950.18,
            'skill_type_id'=> 11450,
            'started_at'=> '2015-06-04T05:41:09Z',
        ]));

        $this->character->agent_research()->save(new CharacterAgentResearch([
            'agent_id'=> 3011474,
            'points_per_day'=> 12.78,
            'remainder_points'=> 35.10,
            'skill_type_id'=> 11446,
            'started_at'=> '2019-04-22T12:25:28Z',
        ]));

        $this->assertCount(3, $this->character->agent_research);
    }

    public function testHasBalance()
    {
        $balance = new CharacterWalletBalance([
            'balance' => 29500.01,
        ]);

        $this->character->balance()->save($balance);

        $this->assertEquals($balance->balance, $this->character->balance->balance);
    }

    public function testHasBlueprints()
    {
        $this->character->blueprints()->save(new CharacterBlueprint([
            'item_id' => 1000000010495,
            'location_flag' => 'Hangar',
            'location_id' => 60014719,
            'material_efficiency' => 0,
            'quantity' => 1,
            'runs' => -1,
            'time_efficiency' => 0,
            'type_id' => 691,
        ]));

        $this->character->blueprints()->save(new CharacterBlueprint([
            'item_id' => 1000000010496,
            'location_flag' => 'Hangar',
            'location_id' => 60014719,
            'material_efficiency' => 5,
            'quantity' => 1,
            'runs' => 17,
            'time_efficiency' => 3,
            'type_id' => 692,
        ]));

        $this->character->blueprints()->save(new CharacterBlueprint([
            'item_id' => 1000000010497,
            'location_flag' => 'Cargo',
            'location_id' => 60014719,
            'material_efficiency' => 1,
            'quantity' => 1,
            'runs' => 3,
            'time_efficiency' => 7,
            'type_id' => 693,
        ]));

        $this->assertCount(3, $this->character->blueprints);
    }

    public function testHasCorporationHistory()
    {
        $this->character->corporation_history()->save(new CharacterCorporationHistory([
            'corporation_id' => 90000001,
            'is_deleted' => true,
            'record_id' => 500,
            'start_date' => '2016-06-26T20:00:00Z',
        ]));

        $this->character->corporation_history()->save(new CharacterCorporationHistory([
            'corporation_id' => 90000002,
            'record_id' => 501,
            'start_date' => '2016-07-26T20:00:00Z',
        ]));

        $this->assertCount(2, $this->character->corporation_history);
    }

    public function testHasFatigue()
    {
        $fatigue = new CharacterFatigue([
            'jump_fatigue_expire_date' => '2017-07-05T15:42:00Z',
            'last_jump_date' => '2017-07-05T15:42:00Z',
            'last_update_date' => '2017-07-05T15:42:00Z',
        ]);

        $this->character->fatigue()->save($fatigue);

        $this->assertEquals($fatigue->last_update_date, $this->character->fatigue->last_update_date);
    }

    public function testHasDefaultFatigue()
    {
        $this->assertNotNull($this->character->fatigue);
    }

    public function testHasLocation()
    {
        $location = new CharacterLocation([
            'solar_system_id' => 30002505,
            'structure_id' => 1000000016989,
        ]);

        $this->character->location()->save($location);

        $this->assertEquals($location->solar_system_id, $this->character->location->solar_system_id);
    }

    public function testIsOnline()
    {
        $online = new CharacterOnline([
            'last_login' => '2017-01-02T03:04:05Z',
            'last_logout' => '2017-01-02T04:05:06Z',
            'logins' => 9001,
            'online' => true,
        ]);

        $this->character->online()->save($online);

        $this->assertEquals($online->online, $this->character->online->online);
    }

    public function testHasSkillpoints()
    {
        $skill_info = new CharacterInfoSkill([
            'total_sp' => 20000,
        ]);

        $this->character->skillpoints()->save($skill_info);

        $this->assertEquals($skill_info->total_sp, $this->character->skillpoints->total_sp);
    }

    public function testHasSkills()
    {
        $this->character->skills()->save(new CharacterSkill([
            'skill_id' => 3,
            'skillpoints_in_skill' => 10000,
            'trained_skill_level' => 4,
            'active_skill_level' => 3,
        ]));

        $this->character->skills()->save(new CharacterSkill([
            'skill_id' => 2,
            'skillpoints_in_skill' => 10000,
            'trained_skill_level' => 1,
            'active_skill_level' => 1,
        ]));

        $this->assertCount(2, $this->character->skills);
    }

    public function testHasShip()
    {
        $ship = new CharacterShip([
            'ship_item_id' => 1000000016991,
            'ship_name' => 'SPACESHIPS!!!',
            'ship_type_id' => 1233,
        ]);

        $this->character->ship()->save($ship);

        $this->assertEquals($ship->ship_name, $this->character->ship->ship_name);
    }
}
