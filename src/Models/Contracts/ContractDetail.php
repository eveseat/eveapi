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

namespace Seat\Eveapi\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;
use Seat\Eveapi\Models\Sde\Constellation;
use Seat\Eveapi\Models\Sde\Region;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

#[OA\Schema(
    title: 'ContractDetail',
    description: 'Contract Detail',
    properties: [
        new OA\Property(property: 'contract_id', description: 'The contract identifier', type: 'integer', format: 'int64'),
        new OA\Property(property: 'type', description: 'The contract type', type: 'string', enum: ['unknown', 'item_exchange', 'auction', 'courier', 'loan']),
        new OA\Property(property: 'status', description: 'The contract status', type: 'string', enum: ['outstanding', 'in_progress', 'finished_issuer', 'finished_contractor', 'cancelled', 'rejected', 'failed', 'deleted', 'reversed']),
        new OA\Property(property: 'title', description: 'The contract description', type: 'string'),
        new OA\Property(property: 'for_corporation', description: 'True if the contract is a corporation contract', type: 'boolean'),
        new OA\Property(property: 'availability', description: 'The contract availability scope', type: 'string', enum: ['public', 'personal', 'corporation', 'alliance']),
        new OA\Property(property: 'date_issued', description: 'The date/time when the contract has been made', type: 'string', format: 'date-time'),
        new OA\Property(property: 'date_expired', description: 'The date/time when the contract is expiring', type: 'string', format: 'date-time'),
        new OA\Property(property: 'date_accepted', description: 'The date/time when the contract has been accepted', type: 'string', format: 'date-time'),
        new OA\Property(property: 'days_to_complete', description: 'The amount of day during which the contract is going (for courier contract)', type: 'integer'),
        new OA\Property(property: 'date_completed', description: 'The date/time when the contract has been completed', type: 'string', format: 'date-time'),
        new OA\Property(property: 'price', description: 'The amount of ISK the acceptor entity must pay to get the contract', type: 'number', format: 'double'),
        new OA\Property(property: 'reward', description: 'The amount of ISK the acceptor entity is earning by accepting the contract', type: 'number', format: 'double'),
        new OA\Property(property: 'collateral', description: 'The amount of ISK the acceptor entity have to pay in case of failure', type: 'number', format: 'double'),
        new OA\Property(property: 'buyout', description: 'The amount of ISK the contract is completed (for auction)', type: 'number', format: 'double'),
        new OA\Property(property: 'volume', description: 'The contract volume', type: 'number', format: 'double'),
        new OA\Property(property: 'issuer', ref: '#/components/schemas/UniverseName'),
        new OA\Property(property: 'assignee', ref: '#/components/schemas/UniverseName'),
        new OA\Property(property: 'acceptor', ref: '#/components/schemas/UniverseName'),
        new OA\Property(property: 'bids', type: 'array', items: new OA\Items(ref: '#/components/schemas/ContractBid')),
        new OA\Property(property: 'lines', type: 'array', items: new OA\Items(ref: '#/components/schemas/ContractItem')),
        new OA\Property(property: 'start_location', ref: '#/components/schemas/UniverseStructure'),
        new OA\Property(property: 'end_location', ref: '#/components/schemas/UniverseStructure'),
    ],
    type: 'object'
)]
class ContractDetail extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $casts = [
        'for_corporation' => 'boolean',
    ];

    /**
     * @var string
     */
    protected $primaryKey = 'contract_id';

    /**
     * Seed model using an ESI data structure.
     *
     * @param  $esi_structure
     * @return $this
     */
    public function fromEsi($esi_structure): ContractDetail
    {
        $this->fill([
            'issuer_id'             => $esi_structure->issuer_id,
            'issuer_corporation_id' => $esi_structure->issuer_corporation_id,
            'assignee_id'           => $esi_structure->assignee_id,
            'acceptor_id'           => $esi_structure->acceptor_id,
            'type'                  => $esi_structure->type,
            'status'                => $esi_structure->status,
            'title'                 => $esi_structure->title ?? null,
            'for_corporation'       => (int) $esi_structure->for_corporation, // enforce raw cast to avoid mutex between db retrieval and model seed
            'availability'          => $esi_structure->availability,
            'date_issued'           => (string) carbon($esi_structure->date_issued), // enforce raw cast to avoid mutex between db retrieval and model seed
            'date_expired'          => (string) carbon($esi_structure->date_expired), // enforce raw cast to avoid mutex between db retrieval and model seed
            'date_accepted'         => isset($esi_structure->date_accepted) ?
                (string) carbon($esi_structure->date_accepted) : null, // enforce raw cast to avoid mutex between db retrieval and model seed
            'days_to_complete'      => $esi_structure->days_to_complete ?? null,
            'date_completed'        => isset($esi_structure->date_completed) ?
                (string) carbon($esi_structure->date_completed) : null, // enforce raw cast to avoid mutex between db retrieval and model seed
            'price'                 => $esi_structure->price ?? null,
            'reward'                => $esi_structure->reward ?? null,
            'collateral'            => $esi_structure->collateral ?? null,
            'buyout'                => $esi_structure->buyout ?? null,
            'volume'                => $esi_structure->volume ?? null,
        ]);

        // update location fields manually (to prevent excessive database queries).
        $this->setStartLocation($esi_structure->start_location_id ?? null);
        $this->setEndLocation($esi_structure->end_location_id ?? null);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function acceptor()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'acceptor_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignee()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'assignee_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function issuer()
    {
        return $this->hasOne(UniverseName::class, 'entity_id', 'issuer_id')
            ->withDefault([
                'name'      => trans('web::seat.unknown'),
                'category'  => 'character',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bids()
    {
        return $this->hasMany(ContractBid::class, 'contract_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(ContractItem::class, 'contract_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function start_location()
    {
        return $this->morphTo()
            ->withDefault(function ($model) {
                $model->name = trans('web::seat.unknown');
                $model->solar_system = new SolarSystem();
                $model->solar_system->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.system', 1));
                $model->solar_system->constellation = new Constellation();
                $model->solar_system->constellation->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.constellation', 1));
                $model->solar_system->region = new Region();
                $model->solar_system->region->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.region', 1));
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function end_location()
    {
        return $this->morphTo()
            ->withDefault(function ($model) {
                $model->name = trans('web::seat.unknown');
                $model->solar_system = new SolarSystem();
                $model->solar_system->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.system', 1));
                $model->solar_system->constellation = new Constellation();
                $model->solar_system->constellation->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.constellation', 1));
                $model->solar_system->region = new Region();
                $model->solar_system->region->name =
                    sprintf('%s %s', trans('web::seat.unknown'), trans_choice('web::moons.region', 1));
            });
    }

    /**
     * Set start Location from a Contract.
     *
     * @param  int|null  $location_id
     */
    private function setStartLocation(?int $location_id)
    {
        $this->start_location_id = $location_id;

        if ($location_id)
            $this->start_location_type = $this->getLocationClass($location_id);
    }

    /**
     * Set end Location from a Contract.
     *
     * @param  int|null  $location_id
     */
    private function setEndLocation(?int $location_id)
    {
        $this->end_location_id = $location_id;

        if ($location_id)
            $this->end_location_type = $this->getLocationClass($location_id);
    }

    /**
     * Return location class according to the location ID.
     *
     * @param  int  $location_id
     * @return string
     */
    private function getLocationClass(int $location_id): string
    {
        // by default, init to a structure.
        $class = UniverseStructure::class;

        // loop over station ranges - if the provided ID is matching one of them, set the class to a station.
        foreach (UniverseStation::STATION_RANGES as $ranges) {
            $start_range = $ranges[0];
            $end_range = $ranges[1];

            if ($location_id >= $start_range && $location_id <= $end_range)
                $class = UniverseStation::class;
        }

        return $class;
    }
}
