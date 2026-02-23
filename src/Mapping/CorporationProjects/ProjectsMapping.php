<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2026 to present Leon Jacobs
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

namespace Seat\Eveapi\Mapping\CorporationProjects;

use Seat\Eveapi\Mapping\DataMapping;

/**
 * Class ProjectsMapping.
 *
 * @package Seat\Eveapi\Mapping\Corporations
 */
class ProjectsMapping extends DataMapping
{
    /**
     * @var array
     */
    protected static $mapping = [
        'id' => 'id',
        'corporation_id' => 'corporation_id',
        'last_modified' => 'last_modified',
        'name' => 'name',
        'progress_current' => 'progress.current',
        'progress_desired' => 'progress.desired',
        'reward_initial' => 'reward.initial',
        'reward_remaining' => 'reward.remaining',
        'state' => 'state',
        'configuration' => 'configuration',
        'contribution_participation_limit' => 'contribution.participation_limit',
        'contribution_reward' => 'contribution.reward_per_contribution',
        'contribution_submission_limit' => 'contribution.submission_limit',
        'contribution_submission_multiplier' => 'contribution.submission_multiplier',
        'creator_id' => 'creator.id',
        'career' => 'details.career',
        'created' => 'details.created',
        'description' => 'details.description',
        'expires' => 'details.expires',
        'finished' => 'details.finished'
     ];
}
