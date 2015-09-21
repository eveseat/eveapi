<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationShareholdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_shareholders', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->enum('shareholderType', ['character', 'corporation']);
            $table->integer('shareholderID');
            $table->string('shareholderName');
            $table->integer('shareholderCorporationID')->nullable();
            $table->string('shareholderCorporationName')->nullable();
            $table->integer('shares');

            // Indexes
            $table->index('shareholderID');
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

        Schema::drop('corporation_shareholders');
    }
}
