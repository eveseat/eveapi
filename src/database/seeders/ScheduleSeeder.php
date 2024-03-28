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

namespace Seat\Eveapi\Database\Seeders;

use Illuminate\Support\Arr;
use Seat\Services\Seeding\AbstractScheduleSeeder;

/**
 * Class ScheduleSeeder.
 *
 * @package Seat\Eveapi\database\seeds
 */
class ScheduleSeeder extends AbstractScheduleSeeder
{
    /**
     * Returns an array of schedules that should be seeded whenever the stack boots up.
     *
     * @return array
     */
    public function getSchedules(): array
    {
        $schedules = [
            [   // ESI Status | Every Minute
                'command' => 'esi:update:status',
                'expression' => '* * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // SDE Data | Monthly
                'command' => 'eve:update:sde',
                'expression' => '0 0 1 * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Update a set of tokens | Every 2 Minutes
                'command' => 'seat:buckets:update',
                'expression' => '*/2 * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Public Data | Daily at 12am
                'command' => 'esi:update:public',
                'expression' => '0 0 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Character Affiliation | Every two hours
                'command' => 'esi:update:affiliations',
                'expression' => '0 */2 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Character Notifications | Every twenty minutes
                'command' => 'esi:update:notifications',
                'expression' => '*/20 * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Killmails | Every fifteen minutes
                'command' => 'esi:update:killmails',
                'expression' => '*/15 * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Contracts | Every fifteen minutes
                'command' => 'esi:update:contracts',
                'expression' => '*/15 * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Prices | Once a day
                'command' => 'esi:update:prices',
                'expression' => '0 13 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Alliances | Once a day
                'command' => 'esi:update:alliances',
                'expression' => '0 14 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Insurances Data | Once a day
                'command' => 'esi:update:insurances',
                'expression' => '0 7 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Sovereignty Data | Once a day
                'command' => 'esi:update:sovereignty',
                'expression' => '0 19 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Stations Data | Once a day
                'command' => 'esi:update:stations',
                'expression' => '0 1 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Horizon remove batches older than 48 hours | Once a day
                'command' => 'queue:prune-batches --hours=48',
                'expression' => '0 3 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
        ];

        // spread out ESI load
        return $this->seedRandomize($schedules);
    }

    /**
     * To prevent massive request wave from all installed instances in the world,
     * we add some randomness to seeded schedules.
     *
     * @see https://github.com/eveseat/seat/issues/731
     */
    private function seedRandomize($schedules): array
    {
        // except utc 11 and utc 12
        $hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];

        foreach ($schedules as $key => $schedule) {
            switch ($schedule['command']) {
                // use random minute - every 2 hours
                case 'esi:update:affiliations':
                    $schedules[$key]['expression'] = sprintf('%d */2 * * *', rand(0, 59));
                    break;
                // use random minute and hour, once a day
                case 'esi:update:public':
                case 'esi:update:prices':
                case 'esi:update:alliances':
                case 'esi:update:insurances':
                case 'esi:update:sovereignty':
                case 'esi:update:stations':
                    $schedules[$key]['expression'] = sprintf('%d %d * * *', rand(0, 59), Arr::random($hours));
                    break;
            }
        }

        return $schedules;
    }

    /**
     * Returns a list of commands to remove from the schedule.
     *
     * @return array
     */
    public function getDeprecatedSchedules(): array
    {
        // these commands are no longer in use, remove them
        return [
            'alerts:run',
            'esi:update:serverstatus',
            'esi:update:esistatus',
            'esi:update:characters',
            'esi:update:corporations',
        ];
    }
}
