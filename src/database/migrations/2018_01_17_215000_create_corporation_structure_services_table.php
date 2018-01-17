<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructureServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_structure_services', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->string('name');
            $table->enum('state', ['online', 'offline', 'cleanup']);

            $table->primary(['corporation_id', 'structure_id', 'name'], 'corporation_structure_services_primary_key');
            $table->index('corporation_id');
            $table->index('structure_id');

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

        Schema::dropIfExists('corporation_structure_services');
    }
}
