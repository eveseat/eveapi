<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefreshTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('refresh_tokens', function (Blueprint $table) {

            $table->integer('character_id')->primary();
            $table->mediumText('refresh_token');
            $table->json('scopes');
            $table->dateTime('expires_on');
            $table->string('token');

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

        Schema::dropIfExists('refresh_tokens');
    }
}
