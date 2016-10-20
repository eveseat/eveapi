<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixStrictmodeNullableColumns extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('character_wallet_journals', function (Blueprint $table) {

            $table->integer('taxReceiverID')->nullable()->change();
            $table->integer('taxAmount')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back from here.
    }
}
