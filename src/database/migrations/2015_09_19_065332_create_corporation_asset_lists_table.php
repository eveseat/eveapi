<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationAssetListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_asset_lists', function (Blueprint $table) {

            $table->integer('corporationID');

            $table->bigInteger('itemID');
            $table->bigInteger('locationID');
            $table->bigInteger('typeID');
            $table->integer('quantity');
            $table->integer('flag');
            $table->boolean('singleton');
            $table->integer('rawQuantity')->default(0);

            // Indexes
            $table->index('corporationID');
            $table->index('locationID');
            $table->index('typeID');

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

        Schema::drop('corporation_asset_lists');
    }
}
