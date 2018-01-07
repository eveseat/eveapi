<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('contract_bids', function (Blueprint $table) {

            $table->integer('bid_id')->primary();
            $table->integer('contract_id');
            $table->foreign('contract_id')
                ->references('contract_id')->on('contract_details');
            $table->integer('bidder_id');
            $table->dateTime('date_bid');
            $table->double('amount');

            $table->index('contract_id');

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

        Schema::dropIfExists('contract_bids');
    }
}
