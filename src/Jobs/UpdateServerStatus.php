<?php

namespace Seat\Eveapi\Jobs;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Seat\Eveapi\Api\Server\ServerStatus;
use Seat\Eveapi\Traits\JobTracker;

/**
 * Class UpdateServerStatus
 * @package Seat\Eveapi\Jobs
 */
class UpdateServerStatus extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs,
        JobTracker;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        //
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

            $job_tracker->output = 'Started ServerStatus Update';
            $job_tracker->save();

            // Call https://api.eveonline.com/server/ServerStatus.xml.aspx
            $work = new ServerStatus();
            $work->call();

            $job_tracker->status = 'Done';
            $job_tracker->output = null;
            $job_tracker->save();

        } catch (\Exception $e) {

            $this->reportJobError($job_tracker, $e);

        }
    }
}
