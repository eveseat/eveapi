<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationIndustryMiningObserversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_industry_mining_observers', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('observer_id');
            $table->dateTime('last_updated');
            $table->enum('observer_type', ['structure']);

            $table->primary(['corporation_id', 'observer_id'], 'observer_primary');
            $table->index('corporation_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('corporation_industry_mining_observers');
    }
}
