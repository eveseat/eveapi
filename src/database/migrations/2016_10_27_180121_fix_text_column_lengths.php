<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixTextColumnLengths extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('character_contact_notifications', function (Blueprint $table) {

            $table->text('messageData')->change();
        });

        Schema::table('character_bookmarks', function (Blueprint $table) {

            $table->text('memo')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back from this.
    }
}
