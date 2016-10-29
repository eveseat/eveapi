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

        // Oh my word! Cant rename any columns in a table that has an
        // enum. o_0
        //
        // Apply the hacky workaround seen here:
        //  https://github.com/laravel/framework/issues/1186#issuecomment-248853309
        Schema::getConnection()->getDoctrineSchemaManager()
            ->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        // Define the tables and columns that need to be changed
        $integer_tables_and_columns = [

            'character_bookmarks'                      => ['itemID'],
            'eve_conquerable_station_lists'            => ['stationID'],
            'character_character_sheets'               => ['homeStationID'],
            'character_contact_lists'                  => ['labelMask'],
            'character_contact_list_labels'            => ['labelID'],
            'character_contact_list_alliances'         => ['labelMask'],
            'character_contact_list_alliance_labels'   => ['labelID'],
            'character_contact_list_corporates'        => ['labelMask'],
            'character_contact_list_corporate_labels'  => ['labelID'],
            'character_contracts'                      => ['startStationID', 'endStationID'],
            'character_industry_jobs'                  => ['stationID', 'blueprintLocationID', 'outputLocationID'],
            'character_market_orders'                  => ['stationID'],
            'character_wallet_journals'                => ['argID1'],
            'character_wallet_transactions'            => ['stationID'],
            'corporation_bookmarks'                    => ['itemID'],
            'corporation_contact_list_labels'          => ['labelID'],
            'corporation_contact_lists'                => ['labelMask'],
            'corporation_contact_list_alliances'       => ['labelMask'],
            'corporation_contact_list_alliance_labels' => ['labelID'],
            'corporation_contracts'                    => ['startStationID', 'endStationID'],
            'corporation_member_securities'            => ['roleID'],
            'corporation_sheets'                       => ['stationID'],
            'corporation_industry_jobs'                => ['stationID', 'blueprintLocationID', 'outputLocationID'],
            'corporation_market_orders'                => ['stationID'],
            'corporation_wallet_journals'              => ['argID1'],
            'corporation_wallet_transactions'          => ['stationID'],

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
