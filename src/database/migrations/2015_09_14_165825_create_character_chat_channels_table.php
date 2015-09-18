<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterChatChannelsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_chat_channels', function (Blueprint $table) {

            $table->integer('characterID');
            $table->integer('channelID');

            // Indexes
            $table->index('characterID');
            $table->index('channelID');

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

        Schema::drop('character_chat_channels');
    }
}
