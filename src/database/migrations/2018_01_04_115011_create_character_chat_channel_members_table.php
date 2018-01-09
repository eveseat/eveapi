<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigInteger('channel_id');
            $table->bigInteger('channel_info_id');
            $table->bigInteger('accessor_id');
            $table->string('accessor_type');
            $table->enum('role', ['allowed', 'operators', 'blocked', 'muted']);
            $table->string('reason')->nullable();
            $table->dateTime('end_at')->nullable();

            $table->index('channel_id');
            $table->index('channel_info_id');
            $table->index('accessor_id');

            $table->foreign('channel_info_id')
                ->references('channel_id')->on('character_chat_channel_infos');

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

        Schema::dropIfExists('character_chat_channel_members');
    }
}
