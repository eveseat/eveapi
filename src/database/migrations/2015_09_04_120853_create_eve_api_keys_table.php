<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEveApiKeysTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eve_api_keys', function (Blueprint $table) {

            $table->integer('key_id')->unique();

            $table->string('v_code', 64);
            $table->integer('user_id');
            $table->tinyInteger('enabled')->default(1);
            $table->text('last_error')->nullable();
            $table->longText('disabled_calls')->nullable();

            // Index
            $table->primary('key_id');
            $table->index('user_id');

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

        Schema::drop('eve_api_keys');
    }
}
