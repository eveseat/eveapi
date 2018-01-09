<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('contract_items', function (Blueprint $table) {

            $table->bigInteger('contract_id');
            $table->bigInteger('record_id');
            $table->integer('type_id');
            $table->integer('quantity');
            $table->integer('raw_quantity')->nullable();
            $table->boolean('is_singleton');
            $table->boolean('is_included');

            $table->primary(['contract_id', 'record_id']);

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

        Schema::dropIfExists('contract_items');
    }
}
