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
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Tests\BaseTestCase;

/**
 * Class AffiliationTest.
 * @package Seat\Eveapi\Tests\Relationship
 */
class AffiliationTest extends BaseTestCase
{
    public function testHasCharacter()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $entity = UniverseName::create([
            'entity_id' => 180548812,
            'category' => 'character',
            'name' => 'CCP Hellmar',
        ]);

        $this->assertEquals($entity->entity_id, $affiliation->character->entity_id);
    }

    public function testHasDefaultCharacter()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $this->assertNotNull($affiliation->character);
    }

    public function testHasCorporation()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $entity = UniverseName::create([
            'entity_id' => 109299958,
            'category' => 'corporation',
            'name' => 'C C P',
        ]);

        $this->assertEquals($entity->entity_id, $affiliation->corporation->entity_id);
    }

    public function testHasDefaultCorporation()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $this->assertNotNull($affiliation->corporation);
    }

    public function testHasAlliance()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
            'alliance_id'    => 434243723,
        ]);

        $entity = UniverseName::create([
            'entity_id' => 434243723,
            'category' => 'alliance',
            'name' => 'C C P Alliance',
        ]);

        $this->assertEquals($entity->entity_id, $affiliation->alliance->entity_id);
    }

    public function testDefaultAlliance()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $this->assertNotNull($affiliation->alliance);
    }

    public function testHasFaction()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
            'faction_id'     => 500001,
        ]);

        $entity = UniverseName::create([
            'entity_id' => 500001,
            'category' => 'faction',
            'name' => 'Caldari State',
        ]);

        $this->assertEquals($entity->entity_id, $affiliation->faction->entity_id);
    }

    public function testHasDefaultFaction()
    {
        $affiliation = CharacterAffiliation::create([
            'character_id'   => 180548812,
            'corporation_id' => 109299958,
        ]);

        $this->assertNotNull($affiliation->faction);
    }
}
