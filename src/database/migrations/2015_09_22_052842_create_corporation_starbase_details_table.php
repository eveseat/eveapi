<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationStarbaseDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbase_details', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->bigInteger('itemID')->unique();
            $table->integer('state');
            $table->dateTime('stateTimestamp');
            $table->dateTime('onlineTimestamp');
            $table->integer('usageFlags');
            $table->integer('deployFlags');
            $table->integer('allowCorporationMembers');
            $table->integer('allowAllianceMembers');
            $table->integer('useStandingsFrom');
            $table->integer('onStandingDrop');
            $table->integer('onStatusDropEnabled');
            $table->integer('onStatusDropStanding');
            $table->integer('onAggression');
            $table->integer('onCorporationWar');
            $table->integer('fuelBlocks')->default(0);
            $table->integer('strontium')->default(0);
            $table->integer('starbaseCharter')->nullable();

            // Indexes
            $table->primary('itemID');
            $table->index('corporationID');

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

        Schema::drop('corporation_starbase_details');
    }
}
