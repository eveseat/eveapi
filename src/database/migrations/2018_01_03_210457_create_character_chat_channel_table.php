<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterChatChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_chat_channels', function (Blueprint $table) {

            $table->integer('character_id');
            $table->integer('channel_id');

            $table->integer('channel_info_id');
            $table->foreign('channel_info_id')
                ->references('channel_id')->on('character_chat_channel_infos');

            $table->index('character_id');
            $table->index('channel_id');

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

        Schema::dropIfExists('character_chat_channels');
    }
}
