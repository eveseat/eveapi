<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterFittingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_fitting_items', function (Blueprint $table) {

            $table->integer('fitting_id');
            // TODO: Fix this foreign key constraint for on delete cascades
//            $table->foreign('fitting_id')
//                ->references('fitting_id')->on('character_fittings')
//                ->onDelete('cascade');
            $table->integer('type_id');
            $table->integer('flag');
            $table->integer('quantity');

            $table->index('fitting_id');
            $table->index('type_id');

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

        Schema::dropIfExists('character_fitting_items');
    }
}
