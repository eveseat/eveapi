<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKillmailVictimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('killmail_victims', function (Blueprint $table) {

            $table->bigInteger('killmail_id')->primary();

            $table->bigInteger('character_id')->nullable();
            $table->bigInteger('corporation_id')->nullable();
            $table->bigInteger('alliance_id')->nullable();
            $table->bigInteger('faction_id')->nullable();
            $table->integer('damage_taken');
            $table->integer('ship_type_id');
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();

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

        Schema::dropIfExists('killmail_victims');
    }
}
