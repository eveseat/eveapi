<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationWalletJournalsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_journals', function (Blueprint $table) {

            $table->string('hash')->unique();

            $table->integer('corporationID');
            $table->integer('accountKey');
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
            $table->integer('owner1TypeID');
            $table->integer('owner2TypeID');

            // Indexes
            $table->primary('hash');
            $table->index('corporationID');
            $table->index('refID');
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

        Schema::drop('corporation_wallet_journals');
    }
}
