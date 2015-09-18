<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEveCharacterInfoEmploymentHistoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_character_info_employment_histories', function (Blueprint $table) {

            $table->integer('characterID');

            $table->integer('recordID');
            $table->integer('corporationID');
            $table->string('corporationName');
            $table->dateTime('startDate');

            // Indexes
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

        Schema::drop('eve_character_info_employment_histories');
    }
}
