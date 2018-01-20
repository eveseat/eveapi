<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('corporation_id');
            $table->integer('division');
            $table->bigInteger('transaction_id');
            $table->dateTime('date');
            $table->integer('type_id');
            $table->bigInteger('location_id');
            $table->double('unit_price');
            $table->integer('quantity');
            $table->integer('client_id');
            $table->boolean('is_buy');
            $table->bigInteger('journal_ref_id');

            $table->primary(['corporation_id', 'division', 'transaction_id'],
                'corporation_wallet_transactions_primary_key');
            $table->index('corporation_id');
            $table->index(['corporation_id', 'division']);
            $table->index('transaction_id');
            $table->index('journal_ref_id');

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

        Schema::dropIfExists('corporation_wallet_transactions');
    }
}
