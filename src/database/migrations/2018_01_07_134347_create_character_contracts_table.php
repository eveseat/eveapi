<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contracts', function (Blueprint $table) {

            $table->integer('character_id');
            $table->integer('contract_id');
            $table->foreign('contract_id')->references('contract_id')->on('contract_details');

            $table->primary(['character_id', 'contract_id']);


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

        Schema::dropIfExists('character_contracts');
    }
}
