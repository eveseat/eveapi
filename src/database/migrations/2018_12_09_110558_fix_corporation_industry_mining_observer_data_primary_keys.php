<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCorporationIndustryMiningObserverDataPrimaryKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->dropPrimary('obeserver_data_primary');
            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id', 'last_updated'],
                'obeserver_data_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporation_industry_mining_observer_data', function (Blueprint $table) {
            $table->dropPrimary('obeserver_data_primary');
            $table->primary(['corporation_id', 'observer_id', 'recorded_corporation_id', 'character_id', 'type_id'],
                'obeserver_data_primary');
        });
    }
}
