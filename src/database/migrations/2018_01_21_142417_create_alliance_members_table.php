<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAllianceMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('alliance_members', function (Blueprint $table) {

            $table->integer('alliance_id');
            $table->bigInteger('corporation_id');

            $table->primary(['alliance_id', 'corporation_id']);
            $table->index('alliance_id');
            $table->index('corporation_id');

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

        Schema::dropIfExists('alliance_members');
    }
}
