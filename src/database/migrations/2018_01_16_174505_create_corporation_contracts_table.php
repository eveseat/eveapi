<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_contracts', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('contract_id');

            $table->primary(['corporation_id', 'contract_id']);
            $table->index('corporation_id');
            $table->index('contract_id');

            $table->foreign('contract_id')->references('contract_id')
                ->on('contract_details');

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

        Schema::dropIfExists('corporation_contracts');
    }
}
