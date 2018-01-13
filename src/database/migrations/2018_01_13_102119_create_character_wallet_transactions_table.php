<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('character_id');
            $table->bigInteger('transaction_id');
            $table->dateTime('date');
            $table->integer('type_id');
            $table->bigInteger('location_id');
            $table->double('unit_price');
            $table->integer('quantity');
            $table->integer('client_id');
            $table->boolean('is_buy');
            $table->boolean('is_personal');
            $table->bigInteger('journal_ref_id');

            $table->primary(['character_id', 'transaction_id'], 'transaction_primary');
            $table->index('character_id');
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

        Schema::dropIfExists('character_wallet_transactions');
    }
}
