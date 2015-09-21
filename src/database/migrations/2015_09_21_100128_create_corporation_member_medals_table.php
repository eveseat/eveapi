<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMemberMedalsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_member_medals', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->integer('medalID');
            $table->integer('characterID');
            $table->string('reason');
            $table->string('status');
            $table->integer('issuerID');
            $table->dateTime('issued');

            // Indexes
            $table->index('corporationID');
            $table->index('medalID');

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

        Schema::drop('corporation_member_medals');
    }
}
