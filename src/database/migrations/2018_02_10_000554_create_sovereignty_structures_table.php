<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSovereigntyStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('sovereignty_structures', function (Blueprint $table) {

            $table->bigInteger('structure_id');
            $table->integer('structure_type_id');
            $table->bigInteger('alliance_id');
            $table->integer('solar_system_id');
            $table->float('vulnerability_occupancy_level')->nullable();
            $table->dateTime('vulnerable_start_time')->nullable();
            $table->dateTime('vulnerable_end_time')->nullable();

            $table->primary('structure_id');
            $table->index('structure_type_id');
            $table->index('alliance_id');
            $table->index('solar_system_id');

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

        Schema::dropIfExists('sovereignty_structures');
    }
}
