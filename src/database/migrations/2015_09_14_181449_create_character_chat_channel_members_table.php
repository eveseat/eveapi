<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterChatChannelMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_chat_channel_members', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('channelID');
            $table->integer('accessorID');
            $table->string('accessorName');
            $table->enum('role', ['allowed', 'blocked', 'muted', 'operators']);
            $table->dateTime('untilWhen')->nullable();
            $table->string('reason')->nullable();

            // Index
            $table->index('channelID');
            $table->index('accessorID');

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

        Schema::drop('character_chat_channel_members');
    }
}
