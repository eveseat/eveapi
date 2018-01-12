<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKillmailAttackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('killmail_attackers', function (Blueprint $table) {

            $table->bigInteger('killmail_id');

            $table->bigInteger('character_id')->nullable();
            $table->bigInteger('corporation_id')->nullable();
            $table->bigInteger('alliance_id')->nullable();
            $table->bigInteger('faction_id')->nullable();
            $table->float('security_status');
            $table->boolean('final_blow');
            $table->integer('damage_done');
            $table->integer('ship_type_id')->nullable();
            $table->integer('weapon_type_id')->nullable();

            $table->index('killmail_id');
            $table->foreign('killmail_id')->references('killmail_id')
                ->on('killmail_details');

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

        Schema::dropIfExists('killmail_attackers');
    }
}
