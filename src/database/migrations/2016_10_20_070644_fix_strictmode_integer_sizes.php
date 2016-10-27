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

        // Start the changes to make the columns accept larger integers
        // Start the changes to make the columns accept larger integers
        Schema::table('character_bookmarks', function (Blueprint $table) {

            $table->bigInteger('itemID')->change();
        });

        Schema::table('eve_conquerable_station_lists', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
        });

        Schema::table('character_character_sheets', function (Blueprint $table) {

            $table->bigInteger('homeStationID')->change();
        });

        Schema::table('character_contact_list_alliances', function (Blueprint $table) {

            $table->bigInteger('labelMask')->change();
        });

        Schema::table('character_contact_list_corporates', function (Blueprint $table) {

            $table->bigInteger('labelMask')->change();
        });

        Schema::table('character_contracts', function (Blueprint $table) {

            $table->bigInteger('startStationID')->change();
            $table->bigInteger('endStationID')->change();
        });

        Schema::table('character_industry_jobs', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
            $table->bigInteger('blueprintLocationID')->change();
            $table->bigInteger('outputLocationID')->change();
            $table->decimal('cost', 30, 2)->change();
        });

        Schema::table('character_market_orders', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
        });

        Schema::table('character_wallet_journals', function (Blueprint $table) {

            $table->bigInteger('argID1')->change();
        });

        Schema::table('character_wallet_transactions', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
        });

        Schema::table('corporation_bookmarks', function (Blueprint $table) {

            $table->bigInteger('itemID')->change();
        });

        Schema::table('corporation_contracts', function (Blueprint $table) {

            $table->bigInteger('startStationID')->change();
            $table->bigInteger('endStationID')->change();
        });

        Schema::table('corporation_member_securities', function (Blueprint $table) {

            $table->bigInteger('roleID')->change();
        });

        Schema::table('corporation_sheets', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
        });

        Schema::table('corporation_industry_jobs', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
            $table->bigInteger('blueprintLocationID')->change();
            $table->bigInteger('outputLocationID')->change();
            $table->decimal('cost', 30, 2)->change();
        });

        Schema::table('corporation_market_orders', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
        });

        Schema::table('corporation_wallet_journals', function (Blueprint $table) {

            $table->bigInteger('argID1')->change();
        });

        Schema::table('corporation_wallet_transactions', function (Blueprint $table) {

            $table->bigInteger('stationID')->change();
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
