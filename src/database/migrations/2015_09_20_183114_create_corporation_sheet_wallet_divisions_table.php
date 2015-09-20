<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationSheetWalletDivisionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_sheet_wallet_divisions', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->integer('accountKey');
            $table->string('description');

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

        Schema::drop('corporation_sheet_wallet_divisions');
    }
}
