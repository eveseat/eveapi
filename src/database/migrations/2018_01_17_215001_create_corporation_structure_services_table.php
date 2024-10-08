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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCorporationStructureServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $records = DB::table('migrations')
            ->where('migration', '2020_04_19_161944_drop_surrogate_key_from_corporation_structures_table')
            ->exists();

        if ($records === false) {
            Schema::table('corporation_structures', function (Blueprint $table) {
                $table->unique(['structure_id']);
            });
        }

        Schema::create('corporation_structure_services', function (Blueprint $table) {

            $table->bigInteger('corporation_id');
            $table->bigInteger('structure_id');
            $table->foreign('structure_id')->references('structure_id')
                ->on('corporation_structures')->onDelete('cascade');
            $table->string('name');
            $table->enum('state', ['online', 'offline', 'cleanup']);

            $table->primary(['corporation_id', 'structure_id', 'name'],
                'corporation_structure_services_primary_key');
            $table->index('corporation_id');
            $table->index('structure_id');

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
        $records = DB::table('migrations')
            ->where('migration', '2020_04_19_161944_drop_surrogate_key_from_corporation_structures_table')
            ->exists();

        if ($records === false) {
            Schema::table('corporation_structures', function (Blueprint $table) {
                $table->dropIndex(['structure_id']);
            });
        }

        Schema::dropIfExists('corporation_structure_services');
    }
}
