<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountToCharacterMinings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('character_minings', function (Blueprint $table) {
            $table->double('average_price')->after('quantity')->default(0.0);
            $table->double('adjusted_price')->after('average_price')->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('character_minings', function (Blueprint $table) {
            $table->dropColumn(['average_price','adjusted_price']);
        });
    }
}
