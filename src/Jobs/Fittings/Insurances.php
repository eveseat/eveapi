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

namespace Seat\Eveapi\Jobs\Fittings;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Fittings\Insurance;

/**
 * Class Fittings.
 * @package Seat\Eveapi\Jobs\FIttings\Character
 */
class Insurances extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/insurance/prices/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var array
     */
    protected $tags = ['public'];

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $insurances = $this->retrieve();

        if ($insurances->isCachedLoad() && Insurance::count() > 0) return;

        // create a temporary table - so it will be easier to handle delta between
        // dropped insurances and created/not-updated ones.

        Schema::create('temp_insurances', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->temporary();
        });

        collect($insurances)->each(function ($insurance) {

            collect($insurance->levels)->each(function ($level) use ($insurance) {

                $model = Insurance::updateOrCreate([
                    'type_id' => $insurance->type_id,
                    'name'    => $level->name,
                ], [
                    'cost'    => $level->cost,
                    'payout'  => $level->payout,
                ]);

                DB::table('temp_insurances')->insert([
                    'id' => $model->id,
                ]);
            });
        });

        // drop old insurances
        DB::table('insurances')
            ->leftJoin('temp_insurances', 'insurances.id', '=', 'temp_insurances.id')
            ->whereNull('temp_insurances.id')
            ->delete();

        Schema::dropIfExists('temp_insurances');
    }
}
