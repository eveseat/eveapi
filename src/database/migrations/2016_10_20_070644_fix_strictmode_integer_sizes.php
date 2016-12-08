<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixStrictmodeIntegerSizes extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Heads up that this is a heavy migration.
        print('Running migration to increase integer size constraints. ' .
            'This may take some time to complete.' . PHP_EOL);

        // Oh my word! Cant rename any columns in a table that has an
        // enum. o_0
        //
        // Apply the hacky workaround seen here:
        //  https://github.com/laravel/framework/issues/1186#issuecomment-248853309
        Schema::getConnection()->getDoctrineSchemaManager()
            ->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        // Define the tables and columns that need to be changed
        $integer_tables_and_columns = [

            'account_api_key_info_characters' => ['characterID', 'corporationID'],

            'character_account_balances'                    => ['characterID'],
            'character_asset_list_contents'                 => ['characterID'],
            'character_asset_lists'                         => ['characterID'],
            'character_bookmarks'                           => ['itemID', 'characterID', 'bookmarkID', 'creatorID', 'mapID'],
            'character_character_sheet_corporation_titles'  => ['characterID', 'titleID'],
            'character_character_sheet_implants'            => ['characterID'],
            'character_character_sheet_jump_clone_implants' => ['jumpCloneID', 'characterID'],
            'character_character_sheet_jump_clones'         => ['jumpCloneID', 'characterID'],
            'character_character_sheet_skills'              => ['characterID'],
            'character_character_sheets'                    => ['characterID', 'corporationID', 'allianceID', 'factionID', 'homeStationID'],
            'character_chat_channel_infos'                  => ['channelID', 'ownerID'],
            'character_chat_channel_members'                => ['channelID', 'accessorID'],
            'character_chat_channels'                       => ['characterID', 'channelID'],
            'character_contact_list_alliance_labels'        => ['characterID', 'labelID'],
            'character_contact_list_alliances'              => ['characterID', 'contactID', 'labelMask'],
            'character_contact_list_corporate_labels'       => ['characterID', 'corporationID', 'labelID'],
            'character_contact_list_corporates'             => ['characterID', 'corporationID', 'contactID', 'labelMask'],
            'character_contact_list_labels'                 => ['characterID', 'labelID'],
            'character_contact_lists'                       => ['characterID', 'contactID', 'labelMask'],
            'character_contact_notifications'               => ['characterID', 'notificationID', 'senderID'],
            'character_contract_items'                      => ['characterID', 'contractID', 'recordID'],
            'character_contracts'                           => ['characterID', 'contractID', 'issuerID', 'issuerCorpID', 'assigneeID', 'acceptorID', 'forCorp', 'startStationID', 'endStationID'],
            'character_industry_jobs'                       => ['characterID', 'jobID', 'installerID', 'facilityID', 'activityID', 'completedCharacterID', 'stationID', 'blueprintLocationID', 'outputLocationID'],
            'character_kill_mails'                          => ['characterID', 'killID'],
            'character_mail_message_bodies'                 => ['messageID'],
            'character_mail_messages'                       => ['characterID', 'messageID', 'senderID', 'toCorpOrAllianceID', 'toListID'],
            'character_mailing_list_infos'                  => ['listID'],
            'character_mailing_lists'                       => ['characterID', 'listID'],
            'character_market_orders'                       => ['charID', 'bid', 'stationID'],
            'character_notifications'                       => ['characterID', 'notificationID', 'senderID'],
            'character_notifications_texts'                 => ['notificationID'],
            'character_planetary_colonies'                  => ['ownerID'],
            'character_planetary_links'                     => ['ownerID'],
            'character_planetary_pins'                      => ['ownerID'],
            'character_planetary_routes'                    => ['ownerID'],
            'character_researches'                          => ['characterID'],
            'character_skill_in_trainings'                  => ['characterID'],
            'character_skill_queues'                        => ['characterID'],
            'character_standings'                           => ['characterID'],
            'character_upcoming_calendar_events'            => ['characterID'],
            'character_wallet_journals'                     => ['characterID', 'ownerID1', 'ownerID2', 'taxReceiverID', 'argID1'],
            'character_wallet_transactions'                 => ['characterID', 'clientID', 'stationID'],

            'corporation_asset_list_contents'          => ['corporationID'],
            'corporation_asset_lists'                  => ['corporationID'],
            'corporation_bookmarks'                    => ['corporationID', 'itemID'],
            'corporation_contact_list_labels'          => ['corporationID', 'labelID'],
            'corporation_contact_lists'                => ['corporationID', 'contactID', 'labelMask'],
            'corporation_contact_list_alliances'       => ['corporationID', 'contactID', 'labelMask'],
            'corporation_contact_list_alliance_labels' => ['corporationID', 'labelID'],
            'corporation_contracts'                    => ['corporationID', 'issuerID', 'issuerCorpID', 'assigneeID', 'acceptorID', 'forCorp', 'startStationID', 'endStationID'],
            'corporation_contract_items'               => ['corporationID', 'contractID', 'recordID'],
            'corporation_customs_office_locations'     => ['corporationID'],
            'corporation_customs_offices'              => ['corporationID'],
            'corporation_industry_jobs'                => ['corporationID', 'jobID', 'installerID', 'facilityID', 'stationID', 'blueprintLocationID', 'outputLocationID'],
            'corporation_kill_mails'                   => ['corporationID', 'killID'],
            'corporation_locations'                    => ['corporationID', 'mapID'],
            'corporation_market_orders'                => ['corporationID', 'charID', 'stationID'],
            'corporation_medals'                       => ['medalID', 'corporationID', 'creatorID'],
            'corporation_member_medals'                => ['corporationID', 'medalID', 'characterID', 'issuerID'],
            'corporation_member_securities'            => ['corporationID', 'characterID', 'roleID'],
            'corporation_member_security_logs'         => ['corporationID', 'characterID', 'issuerID'],
            'corporation_member_security_titles'       => ['corporationID', 'characterID'],
            'corporation_member_trackings'             => ['corporationID', 'characterID', 'locationID'],
            'corporation_shareholders'                 => ['corporationID', 'shareholderID', 'shareholderCorporationID'],
            'corporation_sheet_divisions'              => ['corporationID'],
            'corporation_sheet_wallet_divisions'       => ['corporationID'],
            'corporation_sheets'                       => ['corporationID', 'stationID', 'ceoID', 'allianceID', 'factionID'],
            'corporation_standings'                    => ['corporationID'],
            'corporation_starbase_details'             => ['corporationID', 'useStandingsFrom'],
            'corporation_starbases'                    => ['corporationID'],
            'corporation_titles'                       => ['corporationID', 'titleID'],
            'corporation_wallet_journals'              => ['argID1', 'corporationID', 'ownerID1', 'ownerID2'],
            'corporation_wallet_transactions'          => ['stationID', 'corporationID', 'clientID', 'characterID'],

            'eve_conquerable_station_lists' => ['stationID'],

        ];

        // Loop over the changes defined in the above array.
        foreach ($integer_tables_and_columns as $table => $columns) {

            Schema::table($table, function (Blueprint $table) use ($columns) {

                // Loop over the columns that are passed in and change them
                foreach ($columns as $column)
                    $table->bigInteger($column)->change();
            });
        }

        // Fix some Wallet values for the industry jobs tables.
        Schema::table('character_industry_jobs', function (Blueprint $table) {

            $table->decimal('cost', 30, 2)->change();
        });

        Schema::table('corporation_industry_jobs', function (Blueprint $table) {

            $table->decimal('cost', 30, 2)->change();
        });

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
