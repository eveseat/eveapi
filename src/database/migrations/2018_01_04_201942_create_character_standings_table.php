<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterStandingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_standings', function (Blueprint $table) {

            $table->increments('id');

            $table->bigInteger('character_id');
            $table->integer('from_id');
            $table->enum('from_type', ['agent', 'npc_corp', 'faction']);
            $table->float('standing');

            $table->index('character_id');
            $table->index('from_id');

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

        Schema::dropIfExists('character_standings');
    }
}
