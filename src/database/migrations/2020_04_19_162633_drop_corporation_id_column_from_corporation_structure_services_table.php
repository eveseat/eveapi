<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2022 Leon Jacobs
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

/**
 * Class DropCorporationIdColumnFromCorporationStructureServicesTable.
 */
class DropCorporationIdColumnFromCorporationStructureServicesTable extends Migration
{
    public function up()
    {
        // remove any duplicated entry base on structure_id
        DB::statement('DELETE a FROM corporation_structure_services a INNER JOIN corporation_structure_services b WHERE a.updated_at < b.updated_at AND a.structure_id = b.structure_id AND a.name = b.name');

        Schema::table('corporation_structure_services', function (Blueprint $table) {
            $table->dropPrimary(['corporation_id', 'structure_id', 'name']);
            $table->primary(['structure_id', 'name']);
            $table->dropColumn('corporation_id');
        });
    }

    public function down()
    {
        Schema::table('corporation_structure_services', function (Blueprint $table) {
            $table->bigInteger('corporation_id')->default(0)->first();
            $table->dropPrimary(['structure_id', 'name']);
            $table->primary(['corporation_id', 'structure_id', 'name'], 'corporation_structure_services_pk');
        });
    }
}
