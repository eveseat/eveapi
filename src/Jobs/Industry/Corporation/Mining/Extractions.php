<?php
/**
 * Created by PhpStorm.
 * User: ASPTT
 * Date: 20/01/2018
 * Time: 08:48
 */

namespace Seat\Eveapi\Jobs\Industry\Corporation\Mining;

use Seat\Eveapi\Jobs\EsiBase;

class Extractions extends EsiBase
{

    // TODO : has to be test

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/mining/extractions/';

    protected $version = 'v1';

    public function handle()
    {

        $extractions = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        collect($extractions)->each(function($extraction){

            CorporationExtraction::firstOrNew([
                'corporation_id'        => $this->getCorporationId(),
                'structure_id'          => $extraction->structure_id,
                'extraction_start_time' => carbon($extraction->extraction_start_time),
            ])->fill([
                'moon_id'               => $extraction->moon_id,
                'chunk_arrival_time'    => carbon($extraction->chunk_arrival_time),
                'natural_decay_time'    => carbon($extraction->natural_decay_time),
            ])->save();

        });

    }

}
