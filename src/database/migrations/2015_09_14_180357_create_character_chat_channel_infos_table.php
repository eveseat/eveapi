<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterChatChannelInfosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_chat_channel_infos', function (Blueprint $table) {

            $table->integer('channelID')->unique();
            $table->integer('ownerID');
            $table->string('ownerName');
            $table->string('displayName');
            $table->string('comparisonKey');
            $table->boolean('hasPassword');
            $table->text('motd');

            // Indexes
            $table->primary('channelID');
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

        Schema::drop('character_chat_channel_infos');
    }
}
