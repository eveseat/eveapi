<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterContactListLabelsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_contact_list_labels', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('labelID');
            $table->string('name');

            // Index
            $table->index('characterID');
            $table->index('labelID');

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

        Schema::drop('character_contact_list_labels');
    }
}
