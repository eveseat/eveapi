<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructureVulnerabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_structure_vulnerabilities', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->foreign('structure_id')->references('structure_id')
                ->on('corporation_structures')->onDelete('cascade');
            $table->enum('type', ['current', 'next']);
            $table->integer('day');
            $table->integer('hour');

            $table->primary(['corporation_id', 'structure_id', 'type', 'day', 'hour'],
                'corporation_structure_vulnerabilities_primary_key');
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

        Schema::dropIfExists('corporation_structure_vulnerabilities');
    }
}
