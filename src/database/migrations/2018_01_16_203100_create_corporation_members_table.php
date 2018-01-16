<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_members', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('character_id');

            $table->primary(['corporation_id', 'character_id'], 'corporation_members_primary_key');
            $table->index('corporation_id');
            $table->index('character_id');

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

        Schema::dropIfExists('corporation_members');
    }
}
