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

namespace Seat\Eveapi\Jobs;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Eveapi\Traits\JobTracker;

/**
 * Class UpdateCharacter
 * @package Seat\Eveapi\Jobs
 */
class UpdateCharacter extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, JobTracker;

    /**
     * The EveApiKey instance
     *
     * @var
     */
    protected $eve_api_key;

    /**
     * Create a new job instance.
     *
     * @param $eve_api_key
     */
    public function __construct($eve_api_key)
    {

        $this->eve_api_key = $eve_api_key;
    }

    public function workers()
    {

        // An array with the possible workers for a
        // character / account API key. The order is
        // significant as some calls rely on a
        // previous one.
        $workers = [

            // The very first call to determine the
            // access mask and characters
            \Seat\Eveapi\Api\Account\AccountStatus::class,

            \Seat\Eveapi\Api\Character\AccountBalance::class,
            \Seat\Eveapi\Api\Character\AssetList::class,
            \Seat\Eveapi\Api\Character\Bookmarks::class,
            \Seat\Eveapi\Api\Character\CharacterSheet::class,
            \Seat\Eveapi\Api\Character\ChatChannels::class,
            \Seat\Eveapi\Api\Character\ContactList::class,
            \Seat\Eveapi\Api\Character\ContactNotifications::class,

            // Contracts are updated first and then the
            // respective items
            \Seat\Eveapi\Api\Character\Contracts::class,
            \Seat\Eveapi\Api\Character\ContractsItems::class,

            \Seat\Eveapi\Api\Character\IndustryJobs::class,
            \Seat\Eveapi\Api\Character\KillMails::class,

            // Mail Messages is called first so that the
            // headers are populated for the body updates.
            // This is also a requirement from CCP's side
            // before the body is callable via the API.
            \Seat\Eveapi\Api\Character\MailMessages::class,
            \Seat\Eveapi\Api\Character\MailBodies::class,

            \Seat\Eveapi\Api\Character\MailingLists::class,
            \Seat\Eveapi\Api\Character\MarketOrders::class,

            // Notifications is called first so that the
            // texts can be updated.
            \Seat\Eveapi\Api\Character\Notifications::class,
            \Seat\Eveapi\Api\Character\NotificationTexts::class,

            // Planetary Interaction relies totally on the
            // Colonies to be up to date
            \Seat\Eveapi\Api\Character\PlanetaryColonies::class,
            \Seat\Eveapi\Api\Character\PlanetaryPins::class,
            \Seat\Eveapi\Api\Character\PlanetaryRoutes::class,
            \Seat\Eveapi\Api\Character\PlanetaryLinks::class,

            \Seat\Eveapi\Api\Character\Research::class,
            \Seat\Eveapi\Api\Character\SkillInTraining::class,
            \Seat\Eveapi\Api\Character\SkillQueue::class,
            \Seat\Eveapi\Api\Character\Standings::class,
            \Seat\Eveapi\Api\Character\UpcomingCalendarEvents::class,
            \Seat\Eveapi\Api\Character\WalletJournal::class,
            \Seat\Eveapi\Api\Character\WalletTransactions::class,
            \Seat\Eveapi\Api\Eve\CharacterInfo::class
        ];

        // Yield the classes as a generator
        foreach ($workers as $worker) {
            yield $worker;
        }

    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {

        // Find the tracking record for this job
        $job_tracker = $this->trackOrDismiss();

        // If no tracking record was returned, we
        // will simply end here.
        if (!$job_tracker)
            return;

        // Do the update work and catch any errors
        // that may come of it.
        try {

            $job_tracker->status = 'Working';
            $job_tracker->save();

            foreach ($this->workers() as $worker) {

                $job_tracker->output = 'Processing: ' . $worker;
                $job_tracker->save();

                // Perform the update
                $work = new $worker;
                $work->setApi($this->eve_api_key)->call();
            }

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
