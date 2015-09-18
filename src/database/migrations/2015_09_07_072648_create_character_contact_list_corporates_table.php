<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterContactListCorporatesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contact_list_corporates', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('corporationID');
            $table->integer('contactID');
            $table->string('contactName');
            $table->integer('standing');
            $table->integer('contactTypeID');
            $table->integer('labelMask');

            // Indexes
            $table->index('characterID');
            $table->index('corporationID');
            $table->index('contactID');

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

        Schema::drop('character_contact_list_corporates');
    }
}
