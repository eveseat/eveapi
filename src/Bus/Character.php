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
use Seat\Eveapi\Jobs\Assets\Character\Assets;
use Seat\Eveapi\Jobs\Assets\Character\Locations;
use Seat\Eveapi\Jobs\Assets\Character\Names;
use Seat\Eveapi\Jobs\Calendar\Attendees;
use Seat\Eveapi\Jobs\Calendar\Detail;
use Seat\Eveapi\Jobs\Calendar\Events;
use Seat\Eveapi\Jobs\Character\Affiliation;
use Seat\Eveapi\Jobs\Character\AgentsResearch;
use Seat\Eveapi\Jobs\Character\Blueprints;
use Seat\Eveapi\Jobs\Character\CorporationHistory;
use Seat\Eveapi\Jobs\Character\Fatigue;
use Seat\Eveapi\Jobs\Character\Info;
use Seat\Eveapi\Jobs\Character\LoyaltyPoints;
use Seat\Eveapi\Jobs\Character\Medals;
use Seat\Eveapi\Jobs\Character\Roles;
use Seat\Eveapi\Jobs\Character\Standings;
use Seat\Eveapi\Jobs\Character\Titles;
use Seat\Eveapi\Jobs\Clones\Clones;
use Seat\Eveapi\Jobs\Clones\Implants;
use Seat\Eveapi\Jobs\Contacts\Character\Contacts;
use Seat\Eveapi\Jobs\Contacts\Character\Labels as ContactLabels;
use Seat\Eveapi\Jobs\Fittings\Character\Fittings;
use Seat\Eveapi\Jobs\Industry\Character\Jobs;
use Seat\Eveapi\Jobs\Industry\Character\Mining;
use Seat\Eveapi\Jobs\Location\Character\Location;
use Seat\Eveapi\Jobs\Location\Character\Online;
use Seat\Eveapi\Jobs\Location\Character\Ship;
use Seat\Eveapi\Jobs\Mail\Labels as MailLabels;
use Seat\Eveapi\Jobs\Mail\MailingLists;
use Seat\Eveapi\Jobs\Mail\Mails;
use Seat\Eveapi\Jobs\Market\Character\History;
use Seat\Eveapi\Jobs\Market\Character\Orders;
use Seat\Eveapi\Jobs\PlanetaryInteraction\Character\Planets;
use Seat\Eveapi\Jobs\Skills\Character\Attributes;
use Seat\Eveapi\Jobs\Skills\Character\Queue;
use Seat\Eveapi\Jobs\Skills\Character\Skills;
use Seat\Eveapi\Jobs\Wallet\Character\Balance;
use Seat\Eveapi\Jobs\Wallet\Character\Journal;
use Seat\Eveapi\Jobs\Wallet\Character\Transactions;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\RefreshToken;
use Throwable;

/**
 * Class Character.
 *
 * @package Seat\Eveapi\Bus
 */
class Character extends Bus
{
    /**
     * @var int
     */
    private int $character_id;

    /**
     * Character constructor.
     *
     * @param  int  $character_id
     * @param  \Seat\Eveapi\Models\RefreshToken|null  $token
     */
    public function __construct(int $character_id, ?RefreshToken $token = null)
    {
        parent::__construct($token);

        $this->character_id = $character_id;
    }

    /**
     * Dispatch jobs.
     *
     * @return void
     */
    public function fire(): void
    {
        $this->addPublicJobs();

        if (! is_null($this->token))
            $this->addAuthenticatedJobs();

        // Character
        $character = CharacterInfo::firstOrNew(
            ['character_id' => $this->character_id],
            ['name' => "Unknown Character : {$this->character_id}"]
        );

        \Illuminate\Support\Facades\Bus::batch([$this->jobs->toArray()])
            ->then(function (Batch $batch) {
                logger()->debug(
                    sprintf('[Batches][%s] Character batch successfully completed.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                    ]);
            })->catch(function (Batch $batch, Throwable $throwable) {
                logger()->error(
                    sprintf('[Batches][%s] An error occurred during Character batch processing.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'error' => $throwable->getMessage(),
                        'trace' => $throwable->getTrace(),
                    ]);
            })->finally(function (Batch $batch) {
                logger()->info(
                    sprintf('[Batches][%s] Character batch executed.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'stats' => [
                            'success' => $batch->totalJobs - $batch->failedJobs,
                            'failed' => $batch->failedJobs,
                            'total' => $batch->totalJobs,
                        ],
                    ]);
            })->onQueue('characters')->name($character->name)->allowFailures()->dispatch();
    }

    /**
     * Seed jobs list with job which did not require authentication.
     *
     * @return void
     */
    protected function addPublicJobs()
    {
        $this->addPublicJob(new Info($this->character_id));
        $this->addPublicJob(new CorporationHistory($this->character_id));
        $this->addPublicJob(new Affiliation([$this->character_id]));
    }

    /**
     * Seed jobs list with job requiring authentication.
     *
     * @return void
     */
    protected function addAuthenticatedJobs()
    {
        $this->addAuthenticatedJob(new Roles($this->token));
        $this->addAuthenticatedJob(new Titles($this->token));
        $this->addAuthenticatedJob(new Clones($this->token));
        $this->addAuthenticatedJob(new Implants($this->token));

        $this->addAuthenticatedJob(new Location($this->token));
        $this->addAuthenticatedJob(new Online($this->token));
        $this->addAuthenticatedJob(new Ship($this->token));

        $this->addAuthenticatedJob(new Attributes($this->token));
        $this->addAuthenticatedJob(new Queue($this->token));
        $this->addAuthenticatedJob(new Skills($this->token));

        // collect military information
        $this->addAuthenticatedJob(new Fittings($this->token));

        $this->addAuthenticatedJob(new Fatigue($this->token));
        $this->addAuthenticatedJob(new Medals($this->token));

        // collect industrial information
        $this->addAuthenticatedJob(new Blueprints($this->token));
        $this->addAuthenticatedJob(new Jobs($this->token));
        $this->addAuthenticatedJob(new Mining($this->token));
        $this->addAuthenticatedJob(new AgentsResearch($this->token));

        // collect financial information
        $this->addAuthenticatedJob(new Orders($this->token));
        $this->addAuthenticatedJob(new History($this->token));
        $this->addAuthenticatedJob(new Planets($this->token));
        $this->addAuthenticatedJob(new Balance($this->token));
        $this->addAuthenticatedJob(new Journal($this->token));
        $this->addAuthenticatedJob(new Transactions($this->token));
        $this->addAuthenticatedJob(new LoyaltyPoints($this->token));

        // collect intel information
        $this->addAuthenticatedJob(new Standings($this->token));
        $this->addAuthenticatedJob(new Contacts($this->token));
        $this->addAuthenticatedJob(new ContactLabels($this->token));

        $this->addAuthenticatedJob(new MailLabels($this->token));
        $this->addAuthenticatedJob(new MailingLists($this->token));
        $this->addAuthenticatedJob(new Mails($this->token));

        // calendar events
        $this->addAuthenticatedJob(new Events($this->token));
        $this->addAuthenticatedJob(new Detail($this->token));
        $this->addAuthenticatedJob(new Attendees($this->token));

        // assets
        $this->addAuthenticatedJob(new Assets($this->token));
        $this->addAuthenticatedJob(new Names($this->token));
        $this->addAuthenticatedJob(new Locations($this->token));
    }
}
