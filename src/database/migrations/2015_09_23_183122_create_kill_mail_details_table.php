<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKillMailDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('kill_mail_details', function (Blueprint $table) {

            $table->integer('killID')->unique();
            $table->integer('solarSystemID');
            $table->dateTime('killTime');
            $table->integer('moonID');

            // Victim Information
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('corporationID');
            $table->string('corporationName');
            $table->integer('allianceID')->nullable();
            $table->string('allianceName')->nullable();
            $table->integer('factionID')->nullable();
            $table->string('factionName')->nullable();
            $table->integer('damageTaken');
            $table->integer('shipTypeID');

            // Indexes
            $table->primary('killID');
            $table->index('characterID');

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

        Schema::drop('kill_mail_details');
    }
}
