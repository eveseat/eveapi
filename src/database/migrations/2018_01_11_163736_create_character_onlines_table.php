<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterOnlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('character_onlines', function (Blueprint $table) {

            $table->bigInteger('character_id')->primary();
            $table->boolean('online');
            $table->dateTime('last_login')->nullable();
            $table->dateTime('last_logout')->nullable();
            $table->integer('logins');

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

        Schema::dropIfExists('character_onlines');
    }
}
