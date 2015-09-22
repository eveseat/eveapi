<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCorporationTitlesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_titles', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('corporationID');
            $table->integer('titleID');

            $table->string('titleName');
            $table->longText('roles');
            $table->longText('grantableRoles');
            $table->longText('rolesAtHQ');
            $table->longText('grantableRolesAtHQ');
            $table->longText('rolesAtBase');
            $table->longText('grantableRolesAtBase');
            $table->longText('rolesAtOther');
            $table->longText('grantableRolesAtOther');

            // Indexes
            $table->index('corporationID');
            $table->index('titleID');

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

        Schema::drop('corporation_titles');
    }
}
