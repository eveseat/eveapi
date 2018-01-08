<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->bigInteger('channel_id')->primary();

            $table->string('name');
            $table->bigInteger('owner_id');
            $table->string('comparison_key');
            $table->boolean('has_password');
            $table->text('motd');

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

        Schema::dropIfExists('character_chat_channel_infos');
    }
}
