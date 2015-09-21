<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationKillMailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_kill_mails', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('killID');

            // Indexes
            $table->index('corporationID');
            $table->index('killID');

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

        Schema::drop('corporation_kill_mails');
    }
}
