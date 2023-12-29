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
 * Class AddUniqueOnStarbaseIdInCorporationStarbasesTable.
 */
class AddUniqueOnStarbaseIdInCorporationStarbasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // before adding a unique constraint, we have to remove duplicates
        $duplicate_rows =DB::table("corporation_starbases")
            ->select('starbase_id', DB::raw('COUNT(*) as count'))
            ->havingRaw('COUNT(*) > 1')
            ->groupBy("starbase_id")
            ->get();
        foreach ($duplicate_rows as $row) {
            DB::table("corporation_starbases")
                ->where('starbase_id',$row->starbase_id)
                ->limit($row->count - 1) // keep one row remaining
                ->orderBy('updated_at')
                ->delete();
        }

        Schema::table('corporation_starbases', function (Blueprint $table) {
            $table->unique(['starbase_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corporation_starbases', function (Blueprint $table) {
            $table->unique(['starbase_id']);
        });
    }
}
