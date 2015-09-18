<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterWalletTransactionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_wallet_transactions', function (Blueprint $table) {

            $table->string('hash')->unique();
            $table->integer('characterID');
            $table->bigInteger('transactionID');
            $table->dateTime('transactionDateTime');
            $table->integer('quantity');
            $table->string('typeName');
            $table->integer('typeID');
            $table->decimal('price', 30, 2);
            $table->integer('clientID');
            $table->string('clientName');
            $table->integer('stationID');
            $table->string('stationName');
            $table->enum('transactionType', ['buy', 'sell']);
            $table->enum('transactionFor', ['personal', 'corporation']);
            $table->bigInteger('journalTransactionID');
            $table->integer('clientTypeID');

            // Indexes
            $table->primary('hash');
            $table->index('characterID');
            $table->index('transactionID');
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

        Schema::drop('character_wallet_transactions');
    }
}
