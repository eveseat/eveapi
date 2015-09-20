<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationContactListAlliancesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_contact_list_alliances', function (Blueprint $table) {

            $table->integer('corporationID');

            $table->integer('contactID');
            $table->string('contactName');
            $table->integer('standing');
            $table->integer('contactTypeID');
            $table->integer('labelMask');

            // Indexes
            $table->index('corporationID');
            $table->index('contactID');

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

        Schema::drop('corporation_contact_list_alliances');
    }
}
