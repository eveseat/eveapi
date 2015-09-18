<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterMailingListsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_mailing_lists', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('listID');

            // Indexes
            $table->index('characterID');
            $table->index('listID');

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

        Schema::drop('character_mailing_lists');
    }
}
