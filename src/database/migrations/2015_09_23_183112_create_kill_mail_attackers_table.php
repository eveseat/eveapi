<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKillMailAttackersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('kill_mail_attackers', function (Blueprint $table) {

            $table->integer('killID');
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('corporationID');
            $table->string('corporationName');
            $table->integer('allianceID')->nullable();
            $table->string('allianceName')->nullable();
            $table->integer('factionID')->nullable();
            $table->string('factionName')->nullable();
            $table->float('securityStatus');
            $table->integer('damageDone');
            $table->boolean('finalBlow');
            $table->integer('weaponTypeID');
            $table->integer('shipTypeID');

            // Indexes
            $table->index('killID');
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

        Schema::drop('kill_mail_attackers');
    }
}
