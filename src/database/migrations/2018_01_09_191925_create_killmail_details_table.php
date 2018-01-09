<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKillmailDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('killmail_details', function (Blueprint $table) {

            $table->bigIncrements('killmail_id');
            $table->dateTime('killmail_time');
            $table->integer('solar_system_id');
            $table->integer('moon_id')->nullable();
            $table->integer('war_id')->nullable();

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

        Schema::dropIfExists('killmail_details');
    }
}
