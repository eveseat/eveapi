<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountApiKeyInfosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('account_api_key_infos', function (Blueprint $table) {

            $table->integer('keyID')->unique();
            $table->integer('accessMask');
            $table->enum('type', ['Account', 'Character', 'Corporation']);
            $table->dateTime('expires')->nullable();

            // Indexes
            $table->primary('keyID');
            $table->index('type');

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

        Schema::drop('account_api_key_infos');
    }
}
