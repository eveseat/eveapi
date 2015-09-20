<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationContractItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_contract_items', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('contractID');
            $table->integer('recordID');
            $table->integer('typeID');
            $table->integer('quantity');
            $table->integer('rawQuantity')->nullable();
            $table->integer('singleton');
            $table->string('included');

            // Indexes
            $table->index('corporationID');
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

        Schema::drop('corporation_contract_items');
    }
}
