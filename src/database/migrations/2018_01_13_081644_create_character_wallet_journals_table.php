<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterWalletJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_wallet_journals', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('ref_id');
            $table->dateTime('date');
            $table->string('ref_type');
            $table->bigInteger('first_party_id')->nullable();
            $table->enum('first_party_type',
                ['character', 'corporation', 'alliance', 'faction', 'system'])->nullable();
            $table->bigInteger('second_party_id')->nullable();
            $table->enum('second_party_type',
                ['character', 'corporation', 'alliance', 'faction', 'system'])->nullable();
            $table->double('amount')->nullable();
            $table->double('balance')->nullable();
            $table->text('reason')->nullable();
            $table->bigInteger('tax_receiver_id')->nullable();
            $table->double('tax')->nullable();

            $table->primary(['character_id', 'ref_id']);
            $table->index('character_id');
            $table->index('ref_id');
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

        Schema::dropIfExists('character_wallet_journals');
    }
}
