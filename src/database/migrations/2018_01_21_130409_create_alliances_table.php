<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlliancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('alliances', function (Blueprint $table) {

            $table->integer('alliance_id');
            $table->string('name')->nullable();
            $table->bigInteger('creator_id')->nullable();
            $table->bigInteger('creator_corporation_id')->nullable();
            $table->string('ticker')->nullable();
            $table->bigInteger('executor_corporation_id')->nullable();
            $table->timestamp('date_founded')->useCurrent();
            $table->integer('faction_id')->nullable();

            $table->primary('alliance_id');
            $table->index('creator_id');
            $table->index('creator_corporation_id');
            $table->index('executor_corporation_id');
            $table->index('faction_id');

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

        Schema::dropIfExists('alliances');
    }
}
