<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterWalletJournalsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_wallet_journals', function (Blueprint $table) {

            $table->string('hash')->unique();
            $table->integer('characterID');
            $table->bigInteger('refID');
            $table->dateTime('date');
            $table->integer('refTypeID');
            $table->string('ownerName1');
            $table->integer('ownerID1');
            $table->string('ownerName2');
            $table->integer('ownerID2');
            $table->string('argName1');
            $table->integer('argID1');
            $table->decimal('amount', 30, 2);
            $table->decimal('balance', 30, 2);
            $table->string('reason');
            $table->integer('taxReceiverID');
            $table->decimal('taxAmount', 30, 2);
            $table->integer('owner1TypeID');
            $table->integer('owner2TypeID');

            // Indexes
            $table->primary('hash');
            $table->index('characterID');
            $table->index('refID');
            $table->index('refTypeID');

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

        Schema::drop('character_wallet_journals');
    }
}
