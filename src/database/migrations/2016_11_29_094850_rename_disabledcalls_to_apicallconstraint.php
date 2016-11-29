<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameDisabledcallsToApicallconstraint extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('eve_api_keys', function (Blueprint $table) {

            $table->dropColumn('disabled_calls');

            $table->text('api_call_constraints')->after('last_error')
                ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('eve_api_keys', function (Blueprint $table) {

            $table->dropColumn('api_call_constraints');

            $table->longText('disabled_calls')->after('last_error')
                ->nullable();

        });
    }
}
