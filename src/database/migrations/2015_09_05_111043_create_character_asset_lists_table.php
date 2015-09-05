<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterAssetListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_asset_lists', function (Blueprint $table) {

//            $table->increments('id');

            $table->integer('characterID');
            $table->bigInteger('itemID');
            $table->bigInteger('locationID');
            $table->bigInteger('typeID');
            $table->integer('quantity');
            $table->integer('flag');
            $table->boolean('singleton');
            $table->integer('rawQuantity')->default(0);

            // Indexes
            $table->index('characterID');
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

        Schema::drop('character_asset_lists');
    }
}
