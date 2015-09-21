<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMemberSecurityLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_member_security_logs', function (Blueprint $table) {

            $table->string('hash')->unique();

            $table->integer('corporationID');
            $table->integer('characterID');
            $table->string('characterName');
            $table->dateTime('changeTime');
            $table->integer('issuerID');
            $table->string('issuerName');
            $table->string('roleLocationType');
            $table->text('oldRoles');
            $table->text('newRoles');

            // Indexes
            $table->primary('hash');
            $table->index('characterID');
            $table->index('corporationID');

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

        Schema::drop('corporation_member_security_logs');
    }
}
