<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterKillmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_killmails', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('killmail_id');
            $table->string('killmail_hash');

            $table->primary(['character_id', 'killmail_id']);
            $table->index('character_id');
            $table->index('killmail_id');

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

        Schema::dropIfExists('character_killmails');
    }
}
