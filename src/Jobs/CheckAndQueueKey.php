<?php

namespace Seat\Eveapi\Jobs;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Seat\Eveapi\Api\Account\APIKeyInfo;
use Seat\Eveapi\Traits\JobTracker;

class CheckAndQueueKey extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs,
        JobTracker;

    protected $eve_api_key;

    /**
     * Create a new job instance.
     *
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

            $job_tracker->output = 'Started APIKeyInfo Update';
            $job_tracker->save();

            // https://api.eveonline.com/account/APIKeyInfo.xml.aspx
            $work = new APIKeyInfo();
            $work->call($this->eve_api_key);

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
