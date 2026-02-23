<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
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

use Illuminate\Bus\Batch;
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
use Seat\Eveapi\Jobs\CorporationProjects\Projects;
use Seat\Eveapi\Jobs\Industry\Corporation\Jobs;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Extractions;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\ObserverDetails;
use Seat\Eveapi\Jobs\Industry\Corporation\Mining\Observers;
use Seat\Eveapi\Jobs\Market\Corporation\History;
use Seat\Eveapi\Jobs\Market\Corporation\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOfficeLocations;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation\CustomsOffices;
use Seat\Eveapi\Jobs\Wallet\Corporation\Balances;
use Seat\Eveapi\Jobs\Wallet\Corporation\Journals;
use Seat\Eveapi\Jobs\Wallet\Corporation\Transactions;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\RefreshToken;
use Throwable;

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
    private int $corporation_id;

    /**
     * Corporation constructor.
     *
     * @param  int  $corporation_id
     * @param  \Seat\Eveapi\Models\RefreshToken|null  $token
     */
    public function __construct(int $corporation_id, ?RefreshToken $token = null)
    {
        parent::__construct($token);

        $this->corporation_id = $corporation_id;
        $this->token = $token;
    }

    /**
     * Fires the command.
     *
     * @return void
     */
    public function fire(): void
    {
        $this->addPublicJobs();

        if (! is_null($this->token))
            $this->addAuthenticatedJobs();

        // Corporation
        $corporation = CorporationInfo::firstOrNew(
            ['corporation_id' => $this->corporation_id],
            ['name' => "Unknown Corporation: {$this->corporation_id}"]
        );

        $batch = \Illuminate\Support\Facades\Bus::batch([$this->jobs->toArray()])
            ->then(function (Batch $batch) {
                logger()->debug(
                    sprintf('[Batches][%s] Corporation batch successfully completed.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                    ]);
            })->catch(function (Batch $batch, Throwable $throwable) {
                logger()->error(
                    sprintf('[Batches][%s] An error occurred during Corporation batch processing.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'error' => $throwable->getMessage(),
                        'trace' => $throwable->getTrace(),
                    ]);
            })->finally(function (Batch $batch) {
                logger()->info(
                    sprintf('[Batches][%s] Corporation batch executed.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'stats' => [
                            'success' => $batch->totalJobs - $batch->failedJobs,
                            'failed' => $batch->failedJobs,
                            'total' => $batch->totalJobs,
                        ],
                    ]);
            })->onQueue('corporations')->name($corporation->name)->allowFailures()->dispatch();
    }

    /**
     * Seed jobs list with job which did not require authentication.
     *
     * @return void
     */
    protected function addPublicJobs()
    {
        $this->addPublicJob(new Info($this->corporation_id));
        $this->addPublicJob(new AllianceHistory($this->corporation_id));
    }

    /**
     * Seed jobs list with job requiring authentication.
     *
     * @return void
     */
    protected function addAuthenticatedJobs()
    {
        $this->addAuthenticatedJob(new Divisions($this->corporation_id, $this->token));

        $this->addAuthenticatedJob(new Roles($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new RoleHistories($this->corporation_id, $this->token));

        $this->addAuthenticatedJob(new Titles($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new MembersTitles($this->corporation_id, $this->token));

        $this->addAuthenticatedJob(new MembersLimit($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Members($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new MemberTracking($this->corporation_id, $this->token));

        $this->addAuthenticatedJob(new Medals($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new IssuedMedals($this->corporation_id, $this->token));

        // collect industrial information
        $this->addAuthenticatedJob(new Blueprints($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Facilities($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Jobs($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Observers($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new ObserverDetails($this->corporation_id, $this->token));

        // collect financial information
        $this->addAuthenticatedJob(new Orders($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new History($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Shareholders($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Balances($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Journals($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Transactions($this->corporation_id, $this->token));

        // collect intel information
        $this->addAuthenticatedJob(new Labels($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Standings($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Contacts($this->corporation_id, $this->token));

        // structures
        $this->addAuthenticatedJob(new Starbases($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new StarbaseDetails($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Structures($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Extractions($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new CustomsOffices($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new CustomsOfficeLocations($this->corporation_id, $this->token));

        // assets
        $this->addAuthenticatedJob(new Assets($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new ContainerLogs($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Locations($this->corporation_id, $this->token));
        $this->addAuthenticatedJob(new Names($this->corporation_id, $this->token));

        // projects
        $this->addAuthenticatedJob(new Projects($this->corporation_id, $this->token));
    }
}
