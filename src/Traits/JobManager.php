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

namespace Seat\Eveapi\Traits;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Seat\Eveapi\Models\JobTracking;

/**
 * Class JobManager
 * @package Seat\Eveapi\Traits
 */
trait JobManager
{

    use DispatchesJobs;

    /**
     * A set of default arguments for a Job
     *
     * @var array
     */
    protected $default_args = [
        'scope'    => null,
        'api'      => null,
        'owner_id' => 0,
    ];

    /**
     * Adds a Job to the queue only if one does not
     * already exist.
     * @param $job
     * @param $args
     *
     * @return mixed
     */
    public function addUniqueJob($job, $args)
    {

        // Merge the arguments with the defaults
        // defined in $default_args
        $args = array_replace($this->default_args, $args);

        // Look for an existing job
        $job_id = JobTracking::where('owner_id', $args['owner_id'])
            ->where('api', $args['api'])
            ->whereIn('status', ['Queued', 'Working'])
            ->value('job_id');

        // Just return if the job already exists
        if ($job_id)
            return $job_id;

        // Add a new job onto the queue...
        $job_id = $this->dispatchFromArray(
            $job, [
                'scope' => $args['scope'],
                'api'   => $args['api']
            ]
        );

        // ...and add tracking information
        JobTracking::create(
            [
                'job_id'   => $job_id,
                'owner_id' => $args['owner_id'],
                'api'      => $args['api'],
                'scope'    => $args['scope'],
                'status'   => 'Queued'
            ]);

        return $job_id;

    }
}