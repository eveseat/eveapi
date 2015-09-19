<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterAssetListContentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_asset_list_contents', function (Blueprint $table) {

            $table->integer('characterID');
            $table->bigInteger('itemID');
            $table->bigInteger('typeID');
            $table->integer('quantity');
            $table->integer('flag');
            $table->boolean('singleton');
            $table->integer('rawQuantity')->default(0);

            // Indexes
            $table->index('characterID');
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

        Schema::drop('character_asset_list_contents');
    }
}
