<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationAccountBalancesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_account_balances', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->integer('accountID');
            $table->integer('accountKey');
            $table->decimal('balance', 30, 2)->nullable();  // Some rich bastards out there

            // Indexes
            $table->index('corporationID');
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

        Schema::drop('corporation_account_balances');
    }
}
