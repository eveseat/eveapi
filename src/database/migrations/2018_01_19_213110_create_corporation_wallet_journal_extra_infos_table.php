<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationWalletJournalExtraInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_wallet_journal_extra_infos', function (Blueprint $table) {

            $table->bigInteger('ref_id')->primary();
            $table->bigInteger('location_id')->nullable();
            $table->bigInteger('transaction_id')->nullable();
            $table->string('npc_name')->nullable();
            $table->integer('npc_id')->nullable();
            $table->integer('destroyed_ship_type_id')->nullable();
            $table->bigInteger('character_id')->nullable();
            $table->bigInteger('corporation_id')->nullable();
            $table->bigInteger('alliance_id')->nullable();
            $table->bigInteger('job_id')->nullable();
            $table->bigInteger('contract_id')->nullable();
            $table->integer('system_id')->nullable();
            $table->integer('planet_id')->nullable();

            $table->foreign('ref_id')->references('ref_id')
                ->on('corporation_wallet_journals')
                ->onDelete('cascade');

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

        Schema::dropIfExists('corporation_wallet_journal_extra_infos');
    }
}
