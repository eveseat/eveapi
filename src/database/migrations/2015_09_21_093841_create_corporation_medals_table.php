<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMedalsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_medals', function (Blueprint $table) {

            $table->integer('medalID')->unique();
            $table->integer('corporationID');
            $table->string('title');
            $table->text('description');
            $table->integer('creatorID');
            $table->dateTime('created');

            // Indexes
            $table->primary('medalID');
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

        Schema::drop('corporation_medals');
    }
}
