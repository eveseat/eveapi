<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUniverseStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('universe_structures', function (Blueprint $table) {

            $table->bigInteger('structure_id');
            $table->string('name');
            $table->integer('solar_system_id');
            $table->integer('type_id')->nullable();
            $table->double('x');
            $table->double('y');
            $table->double('z');

            $table->primary('structure_id');
            $table->index('solar_system_id');
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

        Schema::dropIfExists('universe_structures');
    }
}
