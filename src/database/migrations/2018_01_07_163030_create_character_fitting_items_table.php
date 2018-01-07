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
            $table->integer('type_id');
            $table->integer('flag');
            $table->integer('quantity');

            $table->primary(['fitting_id', 'type_id', 'flag']);
            $table->index('fitting_id');
            $table->index('type_id');
            $table->index('flag');

            $table->timestamps();

            $table->foreign('fitting_id')
                  ->references('fitting_id')
                  ->on('character_fittings')
                  ->onDelete('cascade');
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
