<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterContractItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contract_items', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('contractID');
            $table->integer('recordID');
            $table->integer('typeID');
            $table->integer('quantity');
            $table->integer('rawQuantity')->nullable();
            $table->integer('singleton');
            $table->string('included');

            // Indexes
            $table->index('characterID');
            $table->index('contractID');

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

        Schema::drop('character_contract_items');
    }
}
