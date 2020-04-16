<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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
use Illuminate\Support\Facades\DB;

/**
 * Class DropAssetsFromOutposts.
 */
class DropAssetsFromOutposts extends Migration
{
    public function up()
    {
        // outposts have been converted into structure on June 5th
        // https://www.eveonline.com/article/p9m8im/2018-06-05-outpost-phase-out-preparation-info-and-deployment-details
        $asset_tables = ['character_assets', 'corporation_assets'];
        $outpost_range = [61000000, 63999999];
        $special_range = [60014861, 60014928];

        foreach ($asset_tables as $table) {

            // collect all assets located in an outpost
            $assets_in_outpost = DB::table($table)
                ->whereBetween('location_id', $outpost_range)
                ->orWhereBetween('location_id', $special_range)
                ->get();

            foreach ($assets_in_outpost as $asset_in_outpost) {

                // collect all assets located in division from that outpost
                $assets_in_division = DB::table($table)->where('location_id', $asset_in_outpost->location_id)->get();

                foreach ($assets_in_division as $asset_in_division) {

                    // collect all assets located in hangar from that outpost
                    $assets_in_container = DB::table($table)->where('location_id', $asset_in_division->location_id)->get();

                    foreach ($assets_in_container as $asset) {

                        // drop all asset inside that container
                        DB::table($table)->where('location_id', $asset->location_id)->delete();
                    }

                    // drop all containers that outpost
                    DB::table($table)->where('location_id', $asset_in_division->location_id)->delete();
                }

                // drop all divisions in that outpost
                DB::table($table)->where('location_id', $asset_in_outpost->location_id)->delete();
            }

            // drop all assets in that outpost
            DB::table($table)->whereBetween('location_id', $outpost_range)->delete();
        }
    }

    public function down()
    {

    }
}
