<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseCorpMemberMedalReasonField extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('corporation_member_medals', function (Blueprint $table) {

            $table->text('reason')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('corporation_member_medals', function (Blueprint $table) {

            $table->string('reason')->change();
        });
    }
}
