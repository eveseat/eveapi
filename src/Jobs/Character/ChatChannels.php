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

namespace Seat\Eveapi\Jobs\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterChatChannel;
use Seat\Eveapi\Models\Character\CharacterChatChannelInfo;
use Seat\Eveapi\Models\Character\CharacterChatChannelMember;

/**
 * Class ChatChannels
 * @package Seat\Eveapi\Jobs\Character
 */
class ChatChannels extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/chat_channels/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-characters.read_chat_channels.v1';

    /**
     * @var array
     */
    protected $tags = ['character', 'chat_channels'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $chat_channels = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        collect($chat_channels)->each(function ($channel) {

            // Update the info about the channel. We add/update
            // this before the association to satisfy the foreign
            // key constraint.
            CharacterChatChannelInfo::firstOrNew([
                'channel_id' => $channel->channel_id,
            ])->fill([
                'name'           => $channel->name,
                'owner_id'       => $channel->owner_id,
                'comparison_key' => $channel->comparison_key,
                'has_password'   => $channel->has_password,
                'motd'           => $channel->motd,
            ])->save();

            // Associate this character with the channel
            CharacterChatChannel::firstOrCreate([
                'character_id'    => $this->getCharacterId(),
                'channel_id'      => $channel->channel_id,
                'channel_info_id' => $channel->channel_id,
            ]);

            // Update members of each role within the channel.
            foreach (['allowed', 'operators', 'blocked', 'muted'] as $role) {

                // Add && update members
                collect($channel->$role)->each(function ($member) use ($channel, $role) {

                    CharacterChatChannelMember::firstOrNew([
                        'accessor_id'     => $member->accessor_id,
                        'channel_id'      => $channel->channel_id,
                        'channel_info_id' => $channel->channel_id,
                    ])->fill([
                        'role'          => $role,
                        'accessor_type' => $member->accessor_type,
                        'reason'        => isset($member->reason) ? $member->reason : null,
                        'end_at'        => isset($member->end_at) ? $member->end_at : null,
                    ])->save();
                });

                // Cleanup old members
                CharacterChatChannelMember::where('channel_id', $channel->channel_id)
                    ->where('role', $role)
                    ->whereNotIn('accessor_id', collect($channel->$role)
                        ->pluck('accessor_id')->flatten()->all())
                    ->delete();
            }

        });
    }
}
