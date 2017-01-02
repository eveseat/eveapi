<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixStrictmodeTimestampColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * The up() method will change the default value of TIMESTAMP
     * and DATETIME columns to NULL.
     *
     * Yes. This is the shittest migration ever.
     *
     * @return void
     */
    public function up()
    {

        // Heads up that this is a heavy migration.
        echo 'Running migration to fix strict mode date constraints. ' .
            'This may take some time to complete.' . PHP_EOL;

        // Temporarily disable the string SQL mode for this session.
        config(['database.connections.mysql.strict' => false]);
        config(['database.connections.mysql.modes' => null]);

        // Reconnect the database connection after we changed the mode.
        DB::reconnect();

        // Define the columns that are up for change.
        $timestamp_columns = ['created_at', 'updated_at'];

        // Define the tables and their columns that should be updated
        $timestamp_tables_and_columns = [

            'account_account_statuses'                      => $timestamp_columns,
            'account_api_key_info_characters'               => $timestamp_columns,
            'account_api_key_infos'                         => $timestamp_columns,
            'api_token_logs'                                => $timestamp_columns,
            'api_tokens'                                    => $timestamp_columns,
            'character_account_balances'                    => $timestamp_columns,
            'character_asset_list_contents'                 => $timestamp_columns,
            'character_asset_lists'                         => $timestamp_columns,
            'character_bookmarks'                           => $timestamp_columns,
            'character_character_sheet_corporation_titles'  => $timestamp_columns,
            'character_character_sheet_implants'            => $timestamp_columns,
            'character_character_sheet_jump_clone_implants' => $timestamp_columns,
            'character_character_sheet_jump_clones'         => $timestamp_columns,
            'character_character_sheet_skills'              => $timestamp_columns,
            'character_character_sheets'                    => $timestamp_columns,
            'character_chat_channel_infos'                  => $timestamp_columns,
            'character_chat_channel_members'                => $timestamp_columns,
            'character_chat_channels'                       => $timestamp_columns,
            'character_contact_list_alliance_labels'        => $timestamp_columns,
            'character_contact_list_alliances'              => $timestamp_columns,
            'character_contact_list_corporate_labels'       => $timestamp_columns,
            'character_contact_list_corporates'             => $timestamp_columns,
            'character_contact_list_labels'                 => $timestamp_columns,
            'character_contact_lists'                       => $timestamp_columns,
            'character_contact_notifications'               => $timestamp_columns,
            'character_contract_items'                      => $timestamp_columns,
            'character_contracts'                           => $timestamp_columns,
            'character_industry_jobs'                       => $timestamp_columns,
            'character_kill_mails'                          => $timestamp_columns,
            'character_mail_message_bodies'                 => $timestamp_columns,
            'character_mail_messages'                       => $timestamp_columns,
            'character_mailing_list_infos'                  => $timestamp_columns,
            'character_mailing_lists'                       => $timestamp_columns,
            'character_market_orders'                       => $timestamp_columns,
            'character_notifications'                       => $timestamp_columns,
            'character_notifications_texts'                 => $timestamp_columns,
            'character_planetary_colonies'                  => $timestamp_columns,
            'character_planetary_links'                     => $timestamp_columns,
            'character_planetary_pins'                      => $timestamp_columns,
            'character_planetary_routes'                    => $timestamp_columns,
            'character_researches'                          => $timestamp_columns,
            'character_skill_in_trainings'                  => $timestamp_columns,
            'character_skill_queues'                        => $timestamp_columns,
            'character_standings'                           => $timestamp_columns,
            'character_upcoming_calendar_events'            => $timestamp_columns,
            'character_wallet_journals'                     => $timestamp_columns,
            'character_wallet_transactions'                 => $timestamp_columns,
            'corporation_account_balances'                  => $timestamp_columns,
            'corporation_asset_list_contents'               => $timestamp_columns,
            'corporation_asset_lists'                       => $timestamp_columns,
            'corporation_bookmarks'                         => $timestamp_columns,
            'corporation_contact_list_alliance_labels'      => $timestamp_columns,
            'corporation_contact_list_alliances'            => $timestamp_columns,
            'corporation_contact_list_labels'               => $timestamp_columns,
            'corporation_contact_lists'                     => $timestamp_columns,
            'corporation_contract_items'                    => $timestamp_columns,
            'corporation_contracts'                         => $timestamp_columns,
            'corporation_customs_office_locations'          => $timestamp_columns,
            'corporation_customs_offices'                   => $timestamp_columns,
            'corporation_industry_jobs'                     => $timestamp_columns,
            'corporation_kill_mails'                        => $timestamp_columns,
            'corporation_locations'                         => $timestamp_columns,
            'corporation_market_orders'                     => $timestamp_columns,
            'corporation_medals'                            => $timestamp_columns,
            'corporation_member_medals'                     => $timestamp_columns,
            'corporation_member_securities'                 => $timestamp_columns,
            'corporation_member_security_logs'              => $timestamp_columns,
            'corporation_member_security_titles'            => $timestamp_columns,
            'corporation_member_trackings'                  => $timestamp_columns,
            'corporation_shareholders'                      => $timestamp_columns,
            'corporation_sheet_divisions'                   => $timestamp_columns,
            'corporation_sheet_wallet_divisions'            => $timestamp_columns,
            'corporation_sheets'                            => $timestamp_columns,
            'corporation_standings'                         => $timestamp_columns,
            'corporation_starbase_details'                  => $timestamp_columns,
            'corporation_starbases'                         => $timestamp_columns,
            'corporation_titles'                            => $timestamp_columns,
            'corporation_wallet_journals'                   => $timestamp_columns,
            'corporation_wallet_transactions'               => $timestamp_columns,
            'eve_alliance_list_member_corporations'         => $timestamp_columns,
            'eve_alliance_lists'                            => $timestamp_columns,
            'eve_api_call_lists'                            => $timestamp_columns,
            'eve_api_keys'                                  => $timestamp_columns,
            'eve_character_info_employment_histories'       => $timestamp_columns,
            'eve_character_infos'                           => $timestamp_columns,
            'eve_conquerable_station_lists'                 => $timestamp_columns,
            'eve_error_lists'                               => $timestamp_columns,
            'eve_ref_types'                                 => $timestamp_columns,
            'failed_jobs'                                   => ['failed_at'],
            'global_settings'                               => $timestamp_columns,
            'job_trackings'                                 => $timestamp_columns,
            'kill_mail_attackers'                           => $timestamp_columns,
            'kill_mail_details'                             => $timestamp_columns,
            'kill_mail_items'                               => $timestamp_columns,
            'map_jumps'                                     => $timestamp_columns,
            'map_kills'                                     => $timestamp_columns,
            'map_sovereignties'                             => $timestamp_columns,
            'notifications'                                 => $timestamp_columns,
            'password_resets'                               => ['created_at'],
            'people'                                        => $timestamp_columns,
            'person_members'                                => $timestamp_columns,
            'schedules'                                     => $timestamp_columns,
            'security_logs'                                 => $timestamp_columns,
            'server_server_statuses'                        => $timestamp_columns,
            'user_login_histories'                          => $timestamp_columns,
            'user_settings'                                 => $timestamp_columns,
            'users'                                         => $timestamp_columns,
        ];

        // Loop over the tables and columns and alter the default value for
        // the columns.
        foreach ($timestamp_tables_and_columns as $table => $columns) {

            foreach ($columns as $column) {

                $sql = 'ALTER TABLE `' . $table . '` CHANGE `' . $column . '` `' . $column .
                    '` TIMESTAMP  NULL  DEFAULT NULL;';

                DB::update($sql);
            }

        }

        // Next, get the datetime columns fixed up
        $datetime_tables_and_columns = [
            'account_account_statuses'                => ['paidUntil', 'createDate'],
            'character_contracts'                     => ['dateIssued', 'dateExpired', 'dateAccepted', 'dateCompleted'],
            'character_bookmarks'                     => ['created'],
            'character_character_sheets'              => [
                'DoB', 'cloneJumpDate', 'lastRespecDate', 'lastTimedRespec', 'remoteStationDate',
                'jumpActivation', 'jumpFatigue', 'jumpLastUpdate',
            ],
            'character_chat_channel_members'          => ['untilWhen'],
            'character_contact_notifications'         => ['sentDate'],
            'character_contracts'                     => ['dateIssued', 'dateExpired', 'dateAccepted', 'dateCompleted'],
            'character_industry_jobs'                 => ['startDate', 'endDate', 'pauseDate', 'completedDate'],
            'character_mail_messages'                 => ['sentDate'],
            'character_market_orders'                 => ['issued'],
            'character_notifications'                 => ['sentDate'],
            'character_planetary_colonies'            => ['lastUpdate'],
            'character_planetary_pins'                => ['lastLaunchTime', 'installTime', 'expiryTime'],
            'character_researches'                    => ['researchStartDate'],
            'character_skill_in_trainings'            => ['currentTQTime', 'trainingEndTime', 'trainingStartTime'],
            'character_skill_queues'                  => ['startTime', 'endTime'],
            'character_upcoming_calendar_events'      => ['eventDate'],
            'character_wallet_journals'               => ['date'],
            'character_wallet_transactions'           => ['transactionDateTime'],
            'corporation_bookmarks'                   => ['created'],
            'corporation_contracts'                   => ['dateIssued', 'dateExpired', 'dateAccepted', 'dateCompleted'],
            'corporation_industry_jobs'               => ['startDate', 'endDate', 'pauseDate', 'completedDate'],
            'corporation_market_orders'               => ['issued'],
            'corporation_medals'                      => ['created'],
            'corporation_member_medals'               => ['issued'],
            'corporation_member_security_logs'        => ['changeTime'],
            'corporation_member_trackings'            => ['startDateTime', 'logonDateTime', 'logoffDateTime'],
            'corporation_starbase_details'            => ['stateTimestamp', 'onlineTimestamp'],
            'corporation_starbases'                   => ['stateTimestamp', 'onlineTimestamp'],
            'corporation_wallet_journals'             => ['date'],
            'corporation_wallet_transactions'         => ['transactionDateTime'],
            'eve_alliance_list_member_corporations'   => ['startDate'],
            'eve_alliance_lists'                      => ['startDate'],
            'eve_character_info_employment_histories' => ['startDate'],
            'eve_character_infos'                     => ['corporationDate', 'nextTrainingEnds', 'allianceDate'],
            'kill_mail_details'                       => ['killTime'],
            'server_server_statuses'                  => ['currentTime'],
        ];

        // Loop over the tables and columns and alter the default value for
        // the columns.
        foreach ($datetime_tables_and_columns as $table => $columns) {

            foreach ($columns as $column) {

                // Change the default value for the column
                $sql = 'ALTER TABLE `' . $table . '` CHANGE `' . $column . '` `' . $column .
                    '` DATETIME  NULL  DEFAULT NULL;';

                DB::update($sql);

                // Update values of 0000-00-00 00:00:00 to NULL
                $sql = 'UPDATE `' . $table . '` SET `' . $column .
                    '` = NULL WHERE `' . $column . '` = \'0000-00-00 00:00:00\';';

                DB::update($sql);
            }

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back from this.
    }
}
