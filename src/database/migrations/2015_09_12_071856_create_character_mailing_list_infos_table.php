<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterMailingListInfosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_mailing_list_infos', function (Blueprint $table) {

            $table->integer('listID')->unique();
            $table->string('displayName');

            // Indexes
            $table->primary('listID');

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

        Schema::drop('character_mailing_list_infos');
    }
}
