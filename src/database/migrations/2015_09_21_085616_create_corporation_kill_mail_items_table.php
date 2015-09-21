<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationKillMailItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_kill_mail_items', function (Blueprint $table) {

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

        Schema::drop('corporation_kill_mail_items');
    }
}
