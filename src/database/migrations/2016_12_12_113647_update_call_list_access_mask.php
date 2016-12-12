<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCallListAccessMask extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('eve_api_call_lists', function (Blueprint $table) {

            $table->bigInteger('accessMask')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('eve_api_call_lists', function (Blueprint $table) {

            $table->integer('accessMask')->change();
        });
    }
}
