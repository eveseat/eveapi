<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationAssetListContentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_asset_list_contents', function (Blueprint $table) {

            $table->integer('corporationID');

            $table->bigInteger('itemID');
            $table->bigInteger('parentAssetItemID')->nullable();
            $table->bigInteger('parentItemID')->nullable();
            $table->bigInteger('typeID');
            $table->integer('quantity');
            $table->integer('flag');
            $table->boolean('singleton');
            $table->integer('rawQuantity')->default(0);

            // Indexes
            $table->index('corporationID');
            $table->index('itemID');
            $table->index('parentAssetItemID');
            $table->index('parentItemID');
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

        Schema::drop('corporation_asset_list_contents');
    }
}
