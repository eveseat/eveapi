<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKillmailVictimItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('killmail_victim_items', function (Blueprint $table) {

            $table->bigInteger('killmail_id');
            $table->integer('item_type_id');
            $table->bigInteger('quantity_destroyed')->nullable();
            $table->bigInteger('quantity_dropped')->nullable();
            $table->boolean('singleton');
            $table->integer('flag');

            $table->primary(['killmail_id', 'item_type_id']);
            $table->index('killmail_id');
            $table->foreign('killmail_id')->references('killmail_id')
                ->on('killmail_details');

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

        Schema::dropIfExists('killmail_victim_items');
    }
}
