<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationKillmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_killmails', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('killmail_id');
            $table->string('killmail_hash');

            $table->primary(['corporation_id', 'killmail_id'], 'corporation_killmails_primary_key');
            $table->index('corporation_id');
            $table->index('killmail_id');

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

        Schema::dropIfExists('corporation_killmails');
    }
}
