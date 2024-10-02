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

/**
 * Class DropSurrogateKeyFromCorporationStructuresTable.
 */
class DropSurrogateKeyFromCorporationStructuresTable extends Migration
{
    public function up()
    {
        $engine = DB::getDriverName();

        switch ($engine) {
            case 'mysql':
                // remove any duplicated entry base on structure_id
                DB::statement('DELETE a FROM corporation_structures a INNER JOIN corporation_structures b WHERE a.updated_at < b.updated_at AND a.structure_id = b.structure_id');
                break;
            case 'pgsql':
            case 'postgresql':
                // remove any duplicated entry base on structure_id
                DB::statement('DELETE FROM corporation_structures a USING corporation_structures b WHERE a.updated_at < b.updated_at AND a.structure_id = b.structure_id');
                break;
        }

        Schema::table('corporation_structures', function (Blueprint $table) {
            $table->dropPrimary(['corporation_id', 'structure_id']);
            $table->primary(['structure_id']);
        });
    }

    public function down()
    {
        Schema::table('corporation_structures', function (Blueprint $table) {
            $table->dropPrimary(['structure_id']);
            $table->primary(['corporation_id', 'structure_id']);
        });
    }
}
