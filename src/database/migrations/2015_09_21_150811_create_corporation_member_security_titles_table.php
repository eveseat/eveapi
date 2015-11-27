<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMemberSecurityTitlesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_member_security_titles', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('characterID');
            $table->string('characterName');

            $table->bigInteger('titleID');
            $table->string('titleName');

            // Indexes
            $table->index('corporationID');
            $table->index('characterID');
            $table->index('titleID');

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

        Schema::drop('corporation_member_security_titles');
    }
}
