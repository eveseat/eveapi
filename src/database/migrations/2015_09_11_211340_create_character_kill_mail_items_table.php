<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCharacterKillMailItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_kill_mail_items', function (Blueprint $table) {

            $table->integer('killID');
            $table->integer('typeID');
            $table->integer('flag');
            $table->integer('qtyDropped');
            $table->integer('qtyDestroyed');
            $table->integer('singleton');

            // Indexes
            $table->index('killID');

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

        Schema::drop('character_kill_mail_items');
    }
}
