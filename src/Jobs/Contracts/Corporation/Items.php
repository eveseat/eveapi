<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Contracts\Corporation;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Contacts\ContractItem;
use Seat\Eveapi\Models\Contacts\CorporationContract;


/**
 * Class Items
 * @package Seat\Eveapi\Jobs\Contracts\Corporation
 */
class Items extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/contracts/{contract_id}/items/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-contracts.read_corporation_contracts.v1';

    /**
     * @var array
     */
    protected $tags = ['corporation', 'contracts', 'items'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $empty_contracts = CorporationContract::join('contract_details',
            'corporation_contracts.contract_id', '=',
            'contract_details.contract_id')
            ->where('corporation_id', $this->getCorporationId())
            ->where('type', '<>', 'courier')
            ->where('status', '<>', 'deleted')
            ->whereNotIn('corporation_contracts.contract_id', function ($query) {

                $query->select('contract_id')
                    ->from('contract_items');

            })
            ->pluck('corporation_contracts.contract_id');

        $empty_contracts->each(function ($contract_id) {

            $items = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
                'contract_id'    => $contract_id,
            ]);

            collect($items)->each(function ($item) use ($contract_id) {

                ContractItem::firstOrCreate([
                    'contract_id'  => $contract_id,
                    'record_id'    => $item->record_id,
                    'type_id'      => $item->type_id,
                    'quantity'     => $item->quantity,
                    'raw_quantity' => $item->raw_quantity ?? null,
                    'is_singleton' => $item->is_singleton,
                    'is_included'  => $item->is_included,
                ]);
            });
        });
    }
}
