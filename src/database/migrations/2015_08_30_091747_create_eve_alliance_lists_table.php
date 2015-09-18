<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEveAllianceListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_alliance_lists', function (Blueprint $table) {

            $table->integer('allianceID')->unique();

            $table->string('name');
            $table->string('shortName');
            $table->integer('executorCorpID');
            $table->integer('memberCount');
            $table->dateTime('startDate');

            // Indexes
            $table->primary('allianceID');

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

        Schema::drop('eve_alliance_lists');
    }
}
