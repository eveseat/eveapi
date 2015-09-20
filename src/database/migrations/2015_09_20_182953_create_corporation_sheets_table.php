<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationSheetsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_sheets', function (Blueprint $table) {

            $table->integer('corporationID')->unique();
            $table->string('corporationName');
            $table->string('ticker');
            $table->integer('ceoID');
            $table->string('ceoName');
            $table->integer('stationID');
            $table->string('stationName');
            $table->text('description');
            $table->string('url');
            $table->integer('allianceID')->nullable();
            $table->integer('factionID')->nullable();
            $table->string('allianceName')->nullable();
            $table->decimal('taxRate', 30, 2);
            $table->integer('memberCount');
            $table->integer('memberLimit');
            $table->integer('shares');
            $table->integer('graphicID');
            $table->integer('shape1');
            $table->integer('shape2');
            $table->integer('shape3');
            $table->integer('color1');
            $table->integer('color2');
            $table->integer('color3');

            // Indexes
            $table->primary('corporationID');
            $table->index('corporationName');

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

        Schema::drop('corporation_sheets');
    }
}
