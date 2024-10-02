<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to present Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporationRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('corporation_roles', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('character_id');
            $table->enum('type', [
                'roles',
                'grantable_roles',
                'roles_at_hq',
                'grantable_roles_at_hq',
                'roles_at_base',
                'grantable_roles_at_base',
                'roles_at_other',
                'grantable_roles_at_other',
            ]);
            $table->string('role');

            $table->primary(['corporation_id', 'character_id', 'type', 'role'],
                'corporation_roles_primary_key');
            $table->index('corporation_id');
            $table->index('character_id');
            $table->index('role');
            $table->index(['type', 'role']);

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

        Schema::dropIfExists('corporation_roles');
    }
}
