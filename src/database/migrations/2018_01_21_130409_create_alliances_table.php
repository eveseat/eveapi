<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->string('name')->default('N/C');
            $table->bigInteger('creator_id')->default(0);
            $table->bigInteger('creator_corporation_id')->default(0);
            $table->string('ticker')->default('N/C');
            $table->bigInteger('executor_corporation_id')->nullable();
            $table->dateTime('date_founded')->default(DB::raw('CURRENT_TIMESTAMP'));
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
