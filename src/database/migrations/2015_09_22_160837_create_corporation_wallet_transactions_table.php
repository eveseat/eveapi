<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationWalletTransactionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_transactions', function (Blueprint $table) {

            $table->string('hash')->unique();
            $table->integer('corporationID');
            $table->integer('accountKey');

            $table->dateTime('transactionDateTime');
            $table->bigInteger('transactionID');
            $table->integer('quantity');
            $table->string('typeName');
            $table->integer('typeID');
            $table->decimal('price', 30, 2);
            $table->integer('clientID');
            $table->string('clientName');
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('stationID');
            $table->string('stationName');
            $table->enum('transactionType', ['buy', 'sell']);
            $table->enum('transactionFor', ['personal', 'corporation']);
            $table->bigInteger('journalTransactionID');
            $table->integer('clientTypeID');

            // Indexes
            $table->primary('hash');
            $table->index('corporationID');
            $table->index('transactionID');
            $table->index('accountKey');

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

        Schema::drop('corporation_wallet_transactions');
    }
}
