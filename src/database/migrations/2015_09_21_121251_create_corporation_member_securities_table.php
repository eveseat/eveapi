<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationMemberSecuritiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_member_securities', function (Blueprint $table) {

            $table->integer('corporationID');
            $table->integer('characterID');
            $table->string('characterName');
            $table->integer('roleID');
            $table->enum('roleType', [
                'roles', 'grantableRoles', 'rolesAtHQ', 'grantableRolesAtHQ',
                'rolesAtBase', 'grantableRolesAtBase', 'rolesAtOther',
                'grantableRolesAtOther'
            ]);
            $table->string('roleName');

            //Indexes
            $table->index('corporationID');
            $table->index('characterID');
            $table->index('roleID');

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

        Schema::drop('corporation_member_securities');
    }
}
