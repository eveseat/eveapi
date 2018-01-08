<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterFittingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_fittings', function (Blueprint $table) {

            $table->bigInteger('character_id');
            $table->bigInteger('fitting_id');
            $table->string('name');
            $table->text('description');
            $table->integer('ship_type_id');

            $table->primary(['character_id', 'fitting_id']);
            $table->index('ship_type_id');
            $table->index('fitting_id');

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

        Schema::dropIfExists('character_fittings');
    }
}
