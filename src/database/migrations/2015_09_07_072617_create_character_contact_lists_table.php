<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterContactListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contact_lists', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('contactID');
            $table->string('contactName');
            $table->integer('standing');
            $table->integer('contactTypeID');
            $table->integer('labelMask');
            $table->boolean('inWatchlist');

            // Indexes
            $table->index('characterID');
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

        Schema::drop('character_contact_lists');
    }
}
