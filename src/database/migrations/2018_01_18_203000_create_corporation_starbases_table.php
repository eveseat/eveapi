<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStarbasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_starbases', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('starbase_id');
            $table->integer('moon_id')->nullable();
            $table->dateTime('onlined_since')->nullable();
            $table->dateTime('reinforced_until')->nullable();
            $table->enum('state', ['offline', 'online', 'onlining', 'reinforced', 'unanchoring'])
                ->nullable();
            $table->integer('type_id');
            $table->integer('system_id');
            $table->dateTime('unanchor_at')->nullable();

            $table->primary(['corporation_id', 'starbase_id'], 'corporation_starbases_primary_key');
            $table->index('corporation_id');
            $table->index('starbase_id');
            $table->index('system_id');
            $table->index('moon_id');
            $table->index('type_id');

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

        Schema::dropIfExists('corporation_starbases');
    }
}
