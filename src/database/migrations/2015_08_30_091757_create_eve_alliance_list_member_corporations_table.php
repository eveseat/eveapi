<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEveAllianceListMemberCorporationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_alliance_list_member_corporations', function (Blueprint $table) {

            $table->integer('allianceID');

            $table->integer('corporationID');
            $table->dateTime('startDate');

            // Indexes
            $table->index('allianceID');
            $table->index('corporationID');

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

        Schema::drop('eve_alliance_list_member_corporations');
    }
}
