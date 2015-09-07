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

namespace Seat\Eveapi\Jobs;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Seat\Eveapi\Api\Account\AccountStatus;
use Seat\Eveapi\Api\Character\AccountBalance;
use Seat\Eveapi\Api\Character\AssetList;
use Seat\Eveapi\Api\Character\CharacterSheet;
use Seat\Eveapi\Api\Character\ContactList;
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

            $job_tracker->output = 'Started AccountStatus Update';
            $job_tracker->save();

            // https://api.eveonline.com/account/AccountStatus.xml.aspx
            $work = new AccountStatus();
            $work->call($this->eve_api_key);

            $job_tracker->output = 'Started AccountBalance Update';
            $job_tracker->save();

            // https://api.eveonline.com/char/AccountBalance.xml.aspx
            $work = new AccountBalance();
            $work->call($this->eve_api_key);

            $job_tracker->output = 'Started AssetList Update';
            $job_tracker->save();

            // https://api.eveonline.com/char/AssetList.xml.aspx
            $work = new AssetList();
            $work->call($this->eve_api_key);

            $job_tracker->output = 'Started CharacterSheet Update';
            $job_tracker->save();

            // https://api.eveonline.com/char/CharacterSheet.xml.aspx
            $work = new CharacterSheet();
            $work->call($this->eve_api_key);

            $job_tracker->output = 'Started ContactList Update';
            $job_tracker->save();

            // https://api.eveonline.com/char/ContactList.xml.aspx
            $work = new ContactList();
            $work->call($this->eve_api_key);

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
