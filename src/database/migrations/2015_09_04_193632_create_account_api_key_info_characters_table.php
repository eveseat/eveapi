<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountApiKeyInfoCharactersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('account_api_key_info_characters', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('keyID');
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('corporationID');
            $table->string('corporationName');

            $table->index('keyID');
            $table->index('characterID');
            $table->index('characterName');

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

        Schema::drop('account_api_key_info_characters');
    }
}
