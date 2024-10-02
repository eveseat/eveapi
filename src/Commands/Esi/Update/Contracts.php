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

namespace Seat\Eveapi\Commands\Esi\Update;

use Illuminate\Console\Command;
use Seat\Eveapi\Jobs\Contracts\Character\Bids as CharacterBids;
use Seat\Eveapi\Jobs\Contracts\Character\Contracts as CharacterContracts;
use Seat\Eveapi\Jobs\Contracts\Character\Items as CharacterItems;
use Seat\Eveapi\Jobs\Contracts\Corporation\Bids as CorporationBids;
use Seat\Eveapi\Jobs\Contracts\Corporation\Contracts as CorporationContracts;
use Seat\Eveapi\Jobs\Contracts\Corporation\Items as CorporationItems;
use Seat\Eveapi\Models\Contracts\CharacterContract;
use Seat\Eveapi\Models\Contracts\ContractDetail;
use Seat\Eveapi\Models\Contracts\CorporationContract;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Contracts.
 *
 * @package Seat\Eveapi\Commands\Esi\Update
 */
class Contracts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'esi:update:contracts {contract_ids?* : Optional contract_ids to update}';

    /**
     * @var string
     */
    protected $description = 'Schedule update jobs for contracts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contract_ids = $this->argument('contract_ids') ?: [];

        // in case requested contract are unknown, enqueue list jobs which will collect all contracts
        if (! ContractDetail::where('status', '<>', 'deleted')->whereIn('contract_id', $contract_ids)->exists()) {
            $this->enqueueContractsListJobs();

            return $this::SUCCESS;
        }

        // collect contract from character related to asked contracts
        $this->enqueueDetailedCharacterContractsJobs($contract_ids);

        // collect contract from corporation related to asked contracts
        $this->enqueueDetailedCorporationContractsJobs($contract_ids);

        return $this::SUCCESS;
    }

    private function enqueueContractsListJobs()
    {
        // process all tokens character contracts by batch of 100
        RefreshToken::chunk(100, function ($tokens) {
            foreach ($tokens as $token) {
                CharacterContracts::dispatch($token);
            }
        });

        // process all tokens corporation contracts with a Director role
        RefreshToken::whereHas('character.affiliation', function ($query) {
            $query->whereNotNull('corporation_id');
        })->whereHas('character.corporation_roles', function ($query) {
            $query->where('scope', 'roles');
            $query->where('role', 'Director');
        })->get()->unique('character.affiliation.corporation_id')->each(function ($token) {
            CorporationContracts::dispatch($token->character->affiliation->corporation_id, $token);
        });
    }

    /**
     * Enqueue relevant detail jobs for requested character contracts.
     *
     * @param  array  $contract_ids
     */
    private function enqueueDetailedCharacterContractsJobs(array $contract_ids)
    {
        CharacterContract::whereIn('contract_id', $contract_ids)
            ->whereHas('detail', function ($query) {
                $query->where('status', '<>', 'deleted');
            })
            ->chunk(200, function ($contracts) {
                $token = null;

                foreach ($contracts as $contract) {

                    if (! $token || $token->character_id != $contract->character_id) {
                        $token = RefreshToken::find($contract->character_id);
                    }

                    if (is_null($token)) {
                        $this->warn(sprintf('No valid token for Character %d - requested by Contract %d',
                            $contract->character_id, $contract->contract_id));
                        continue;
                    }

                    // for each non deleted contract, enqueue relevant detailled jobs
                    if ($contract->detail->type == 'auction')
                        CharacterBids::dispatch($token, $contract->contract_id);

                    if ($contract->detail->type != 'courier' && $contract->detail->volume > 0)
                        CharacterItems::dispatch($token, $contract->contract_id);
                }
            });
    }

    /**
     * Enqueue relevant detail jobs for requested corporation contracts.
     *
     * @param  array  $contract_ids
     */
    private function enqueueDetailedCorporationContractsJobs(array $contract_ids)
    {
        CorporationContract::whereIn('contract_id', $contract_ids)
            ->whereHas('detail', function ($query) {
                $query->where('status', '<>', 'deleted');
            })
            ->chunk(200, function ($contracts) {
                $token = null;

                foreach ($contracts as $contract) {

                    // attempt to locate a token for the required corporation
                    if (! $token || $token->character->affiliation->corporation_id != $contract->corporation_id) {
                        $token = RefreshToken::whereHas('character', function ($query) use ($contract) {
                            $query->whereHas('affiliation', function ($query) use ($contract) {
                                $query->where('corporation_id', $contract->corporation_id);
                            });
                            $query->whereHas('corporation_roles', function ($query) {
                                $query->where('scope', 'roles');
                                $query->where('role', 'Director');
                            });
                        })->first();
                    }

                    if (is_null($token)) {
                        $this->warn(sprintf('No valid token for Corporation %d - requested by Contract %d',
                            $contract->corporation_id, $contract->contract_id));
                        continue;
                    }

                    // for each non deleted contract, enqueue relevant detailled jobs
                    if ($contract->detail->type == 'auction')
                        CorporationBids::dispatch($contract->corporation_id, $token, $contract->contract_id);

                    if ($contract->detail->type != 'courier' && $contract->detail->volume > 0)
                        CorporationItems::dispatch($contract->corporation_id, $token, $contract->contract_id);
                }
            });
    }
}
