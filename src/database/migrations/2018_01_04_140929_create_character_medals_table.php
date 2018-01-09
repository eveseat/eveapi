<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterMedalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_medals', function (Blueprint $table) {

            $table->increments('id');

            $table->bigInteger('character_id');
            $table->integer('medal_id');
            $table->string('title');
            $table->text('description');
            $table->bigInteger('corporation_id');
            $table->bigInteger('issuer_id');
            $table->dateTime('date');
            $table->text('reason');
            $table->enum('status', ['public', 'private']);
            $table->json('graphics');

            $table->index('character_id');
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

        Schema::dropIfExists('character_medals');
    }
}
