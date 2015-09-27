<?php
/*
This file is part of SeAT

Copyright (C) 2015  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace Seat\Eveapi\Api\Character;

use Seat\Eveapi\Api\Base;
use Seat\Eveapi\Models\Character\ChatChannel;
use Seat\Eveapi\Models\Character\ChatChannelInfo;
use Seat\Eveapi\Models\Character\ChatChannelMember;

/**
 * Class ChatChannels
 * @package Seat\Eveapi\Api\Character
 */
class ChatChannels extends Base
{

    /**
     * Run the Update
     *
     * @return mixed|void
     */
    public function call()
    {

        $pheal = $this->setScope('char')->getPheal();

        foreach ($this->api_info->characters as $character) {

            $result = $pheal->ChatChannels([
                'characterID' => $character->characterID]);

            foreach ($result->channels as $channel) {

                // Characters have some form of affiliation with
                // chat channels. This can be either as an owner,
                // allowed, blocked, muted or an operator.
                // Regardless of the applicable role, the character
                // is affiliated to it, and should therefore have
                // the link to the channels details.
                ChatChannel::firstOrCreate([
                    'characterID' => $character->characterID,
                    'channelID'   => $channel->channelID
                ]);

                // Check and update the channels details such as
                // owner information as well as the motd
                $channel_info = ChatChannelInfo::firstOrNew([
                    'channelID' => $channel->channelID]);

                // TODO: Check if the current MOTD and the one that
                // was received in the response differs and record
                // it in a history table.

                $channel_info->fill([
                    'ownerID'       => $channel->ownerID,
                    'ownerName'     => $channel->ownerName,
                    'displayName'   => $channel->displayName,
                    'comparisonKey' => $channel->comparisonKey,
                    'hasPassword'   => $channel->hasPassword,
                    'motd'          => $channel->motd
                ]);

                $channel_info->save();

                // Process the membership information for this chat
                // channel. Members could have a number of roles.
                // The response XML layout and the database differ
                // a little and has been mostly collapsed.

                // We will also cleanup members that are no longer in
                // the chat channel. For this we will keep record of
                // members that *are* in the channel based on the API
                // response in an array. Once we are done updating
                // the members then we will delete the ones that are
                // not mentioned at all.
                $existing_members = [];

                // Allowed Members
                foreach ($channel->allowed as $allowed)
                    array_push(
                        $existing_members, $this->_process_members(
                        $channel->channelID, $allowed, 'allowed'
                    ));

                // Blocked Members
                foreach ($channel->blocked as $blocked)
                    array_push(
                        $existing_members, $this->_process_members(
                        $channel->channelID, $blocked, 'blocked'
                    ));

                // Muted Members
                foreach ($channel->muted as $muted)
                    array_push(
                        $existing_members, $this->_process_members(
                        $channel->channelID, $muted, 'muted'
                    ));

                // Operating Members
                foreach ($channel->operators as $operators)
                    array_push(
                        $existing_members, $this->_process_members(
                        $channel->channelID, $operators, 'operators'
                    ));

                // Cleanup the channel
                ChatChannelMember::where('channelID', $channel->channelID)
                    ->whereNotIn('accessorID', $existing_members)
                    ->delete();

            }

            // Cleanup any channels where there is no longer any
            // affiliation. This may happen in cases where
            // operator access is removed etc.
            ChatChannel::where('characterID', $character->characterID)
                ->whereNotIn('channelID', array_map(function ($channel) {

                    return $channel->channelID;

                }, (array)$result->channels))
                ->delete();
        }

        return;
    }

    /**
     * Process members of a chat channels, updating / adding
     * them and their access
     *
     * @param $channel_id
     * @param $member
     * @param $role
     */
    public function _process_members($channel_id, $member, $role)
    {

        // Get or create the record...
        $member_info = ChatChannelMember::firstOrNew([
            'channelID'  => $channel_id,
            'accessorID' => $member->accessorID]);

        // ... and set its fields
        $member_info->fill([
            'accessorName' => $member->accessorName,
            'role'         => $role,
            'untilWhen'    => isset($member->untilWhen) ?
                $member->untilWhen : null,
            'reason'       => isset($member->reason) ?
                $member->reason : null
        ]);

        $member_info->save();

        // Return the members ID for the cleanup
        return $member->accessorID;
    }
}
