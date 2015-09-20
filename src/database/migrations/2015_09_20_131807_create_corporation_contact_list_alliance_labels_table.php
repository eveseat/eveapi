<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationContactListAllianceLabelsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_contact_list_alliance_labels', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('labelID');
            $table->string('name');

            // Index
            $table->index('corporationID');
            $table->index('labelID');

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

        Schema::drop('corporation_contact_list_alliance_labels');
    }
}
