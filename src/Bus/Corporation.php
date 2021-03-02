<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Bus;

use Seat\Eveapi\Jobs\Assets\Corporation\Assets;
use Seat\Eveapi\Jobs\Assets\Corporation\Locations;
use Seat\Eveapi\Jobs\Assets\Corporation\Names;
use Seat\Eveapi\Jobs\Contacts\Corporation\Contacts;
use Seat\Eveapi\Jobs\Contacts\Corporation\Labels;
use Seat\Eveapi\Jobs\Corporation\AllianceHistory;
use Seat\Eveapi\Jobs\Corporation\Blueprints;
use Seat\Eveapi\Jobs\Corporation\ContainerLogs;
use Seat\Eveapi\Jobs\Corporation\Divisions;
use Seat\Eveapi\Jobs\Corporation\Facilities;
use Seat\Eveapi\Jobs\Corporation\Info;
use Seat\Eveapi\Jobs\Corporation\IssuedMedals;
use Seat\Eveapi\Jobs\Corporation\Medals;
use Seat\Eveapi\Jobs\Corporation\Members;
use Seat\Eveapi\Jobs\Corporation\MembersLimit;
use Seat\Eveapi\Jobs\Corporation\MembersTitles;
use Seat\Eveapi\Jobs\Corporation\MemberTracking;
use Seat\Eveapi\Jobs\Corporation\RoleHistories;
use Seat\Eveapi\Jobs\Corporation\Roles;
use Seat\Eveapi\Jobs\Corporation\Shareholders;
use Seat\Eveapi\Jobs\Corporation\Standings;
use Seat\Eveapi\Jobs\Corporation\StarbaseDetails;
use Seat\Eveapi\Jobs\Corporation\Starbases;
use Seat\Eveapi\Jobs\Corporation\Structures;
use Seat\Eveapi\Jobs\Corporation\Titles;
use Seat\Eveapi\Jobs\Industry\Corporation\Jobs;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Extractions;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\ObserverDetails;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Observers;
use Seat\Eveapi\Jobs\Market\Corporation\History;
use Seat\Eveapi\Jobs\Market\Corporation\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOfficeLocations;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOffices;
use Seat\Eveapi\Jobs\Universe\CorporationStructures;
use Seat\Eveapi\Jobs\Wallet\Corporation\Balances;
use Seat\Eveapi\Jobs\Wallet\Corporation\Journals;
use Seat\Eveapi\Jobs\Wallet\Corporation\Transactions;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Corporation.
 *
 * @package Seat\Eveapi\Bus
 */
class Corporation extends Bus
{
    /**
     * @var int
     */
    private $corporation_id;

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    private $token;

    /**
     * Corporation constructor.
     *
     * @param int $corporation_id
     * @param \Seat\Eveapi\Models\RefreshToken|null $token
     */
    public function __construct(int $corporation_id, ?RefreshToken $token = null)
    {
        parent::__construct();

        $this->corporation_id = $corporation_id;
        $this->token = $token;
    }

    /**
     * Fires the command.
     *
     * @return void
     */
    public function fire()
    {
        $this->addPublicJobs();

        if (! is_null($this->token))
            $this->addAuthenticatedJobs();

        // Corporation
        Info::withChain($this->jobs->toArray())
            ->dispatch($this->corporation_id)
            ->delay(now()->addSeconds(rand(120, 300)));
        // in order to prevent ESI to receive massive income of all existing SeAT instances in the world
        // add a bit of randomize when job can be processed - we use seconds here, so we have more flexibility
        // https://github.com/eveseat/seat/issues/731
    }

    /**
     * Seed jobs list with job which did not require authentication.
     *
     * @return void
     */
    protected function addPublicJobs()
    {
        $this->jobs->add(new AllianceHistory($this->corporation_id));
    }

    /**
     * Seed jobs list with job requiring authentication.
     *
     * @return void
     */
    protected function addAuthenticatedJobs()
    {
        $this->jobs->add(new Divisions($this->corporation_id, $this->token));

        $this->jobs->add(new Roles($this->corporation_id, $this->token));
        $this->jobs->add(new RoleHistories($this->corporation_id, $this->token));

        $this->jobs->add(new Titles($this->corporation_id, $this->token));
        $this->jobs->add(new MembersTitles($this->corporation_id, $this->token));

        $this->jobs->add(new MembersLimit($this->corporation_id, $this->token));
        $this->jobs->add(new Members($this->corporation_id, $this->token));
        $this->jobs->add(new MemberTracking($this->corporation_id, $this->token));

        $this->jobs->add(new Medals($this->corporation_id, $this->token));
        $this->jobs->add(new IssuedMedals($this->corporation_id, $this->token));

        // collect industrial information
        $this->jobs->add(new Blueprints($this->corporation_id, $this->token));
        $this->jobs->add(new Facilities($this->corporation_id, $this->token));
        $this->jobs->add(new Jobs($this->corporation_id, $this->token));
        $this->jobs->add(new Observers($this->corporation_id, $this->token));
        $this->jobs->add(new ObserverDetails($this->corporation_id, $this->token));

        // collect financial information
        $this->jobs->add(new Orders($this->corporation_id, $this->token));
        $this->jobs->add(new History($this->corporation_id, $this->token));
        $this->jobs->add(new Shareholders($this->corporation_id, $this->token));
        $this->jobs->add(new Balances($this->corporation_id, $this->token));
        $this->jobs->add(new Journals($this->corporation_id, $this->token));
        $this->jobs->add(new Transactions($this->corporation_id, $this->token));

        // collect intel information
        $this->jobs->add(new Labels($this->corporation_id, $this->token));
        $this->jobs->add(new Standings($this->corporation_id, $this->token));
        $this->jobs->add(new Contacts($this->corporation_id, $this->token));

        // structures
        $this->jobs->add(new Starbases($this->corporation_id, $this->token));
        $this->jobs->add(new StarbaseDetails($this->corporation_id, $this->token));
        $this->jobs->add(new Structures($this->corporation_id, $this->token));
        $this->jobs->add(new Extractions($this->corporation_id, $this->token));
        $this->jobs->add(new CustomsOffices($this->corporation_id, $this->token));
        $this->jobs->add(new CustomsOfficeLocations($this->corporation_id, $this->token));

        // assets
        $this->jobs->add(new Assets($this->corporation_id, $this->token));
        $this->jobs->add(new ContainerLogs($this->corporation_id, $this->token));
        $this->jobs->add(new Locations($this->corporation_id, $this->token));
        $this->jobs->add(new Names($this->corporation_id, $this->token));
        $this->jobs->add(new CorporationStructures($this->corporation_id, $this->token));
    }
}
