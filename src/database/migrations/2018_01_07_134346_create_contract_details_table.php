<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('contract_details', function (Blueprint $table) {

            $table->integer('contract_id')->primary();
            $table->integer('issuer_id');
            $table->integer('issuer_corporation_id');
            $table->integer('assignee_id');
            $table->integer('acceptor_id');
            $table->bigInteger('start_location_id')->nullable();
            $table->bigInteger('end_location_id')->nullalbe();
            $table->enum('type', [
                'unknown', 'item_exchange', 'auction', 'courier', 'loan',
            ]);
            $table->enum('status', [
                'outstanding', 'in_progress', 'finished_issuer', 'finished_contractor',
                'finished', 'cancelled', 'rejected', 'failed', 'deleted', 'reversed',
            ]);
            $table->string('title')->nullable();
            $table->boolean('for_corporation');
            $table->enum('availability', ['public', 'personal', 'corporation', 'alliance']);
            $table->dateTime('date_issued');
            $table->dateTime('date_expired');
            $table->dateTime('date_accepted')->nullable();
            $table->integer('days_to_complete')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->double('price')->nullable();
            $table->double('reward')->nullable();
            $table->double('collateral')->nullable();
            $table->double('buyout')->nullable();
            $table->float('volume')->nullable();

            $table->index('issuer_id');
            $table->index('issuer_corporation_id');
            $table->index('assignee_id');
            $table->index('acceptor_id');
            $table->index('availability');
            $table->index('date_issued');
            $table->index('date_expired');

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

        Schema::dropIfExists('contract_details');
    }
}
