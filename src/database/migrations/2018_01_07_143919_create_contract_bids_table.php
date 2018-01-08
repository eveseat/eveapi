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

            $table->bigInteger('bid_id')->primary();
            $table->bigInteger('contract_id');

            $table->bigInteger('bidder_id');
            $table->dateTime('date_bid');
            $table->double('amount');

            $table->index('contract_id');

            $table->timestamps();

	        $table->foreign('contract_id')
	              ->references('contract_id')
	              ->on('contract_details');
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
