<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationWalletJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_journals', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->integer('division');
            $table->bigInteger('id');
            $table->dateTime('date');
            $table->string('ref_type');
            $table->bigInteger('first_party_id')->nullable();
            $table->bigInteger('second_party_id')->nullable();
            $table->double('amount')->nullable();
            $table->double('balance')->nullable();
            $table->text('reason')->nullable();
            $table->bigInteger('tax_receiver_id')->nullable();
            $table->double('tax')->nullable();
            // introduced in version 3
            $table->bigInteger('context_id')->nullable();
            $table->enum('context_id_type',
                ['structure_id', 'station_id', 'market_transaction_id', 'character_id', 'corporation_id', 'alliance_id',
                 'eve_system', 'industry_job_id', 'contract_id', 'planet_id', 'system_id', 'type_id'])->nullable();
            $table->string('description');

            $table->primary(['corporation_id', 'division', 'id'],
                'corporation_wallet_journals_primary_key');
            $table->index('corporation_id');
            $table->index(['corporation_id', 'division']);
            $table->index('id');
            $table->index('date');

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

        Schema::dropIfExists('corporation_wallet_journals');
    }
}
