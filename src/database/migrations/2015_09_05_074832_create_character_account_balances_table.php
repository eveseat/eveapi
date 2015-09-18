<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterAccountBalancesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_account_balances', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('characterID');
            $table->integer('accountID');
            $table->integer('accountKey');
            $table->decimal('balance', 30, 2)->nullable();  // Some rich bastards out there

            // Indexes
            $table->index('characterID');

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

        Schema::drop('character_account_balances');
    }
}
