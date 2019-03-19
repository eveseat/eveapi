<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018, 2019  Leon Jacobs
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

class CharacterFittingEndpointV2 extends Migration
{
    const FITTING_LOCATION_FLAG = [
        5   => "Cargo",
        11  => "LoSlot0",
        12  => "LoSlot1",
        13  => "LoSlot2",
        14  => "LoSlot3",
        15  => "LoSlot4",
        16  => "LoSlot5",
        17  => "LoSlot6",
        18  => "LoSlot7",
        19  => "MedSlot0",
        20  => "MedSlot1",
        21  => "MedSlot2",
        22  => "MedSlot3",
        23  => "MedSlot4",
        24  => "MedSlot5",
        25  => "MedSlot6",
        26  => "MedSlot7",
        27  => "HiSlot0",
        28  => "HiSlot1",
        29  => "HiSlot2",
        30  => "HiSlot3",
        31  => "HiSlot4",
        32  => "HiSlot5",
        33  => "HiSlot6",
        34  => "HiSlot7",
        87  => "DroneBay",
        92  => "RigSlot0",
        93  => "RigSlot1",
        94  => "RigSlot2",
        125 => "SubSystemSlot0",
        126 => "SubSystemSlot1",
        127 => "SubSystemSlot2",
        128 => "SubSystemSlot3",
        129 => "SubSystemSlot4",
        158 => "FighterBay",
        164 => "ServiceSlot0",
        165 => "ServiceSlot1",
        166 => "ServiceSlot2",
        167 => "ServiceSlot3",
        168 => "ServiceSlot4",
        169 => "ServiceSlot5",
        170 => "ServiceSlot6",
        171 => "ServiceSlot7",
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('character_fitting_items', function (Blueprint $table) {

            $table->string('flag', 20)->change();

        });

        // iterate over the fitting flag enum binding and update values
        foreach (self::FITTING_LOCATION_FLAG as $flag_id => $flag_string) {
            DB::table('character_fitting_items')->where('flag', (string) $flag_id)
                ->update([
                    'flag' => $flag_string,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // iterate over the fitting flag enum binding and update values
        foreach (self::FITTING_LOCATION_FLAG as $flag_id => $flag_string) {
            DB::table('character_fitting_items')->where('flag', $flag_string)
                ->update([
                    'flag' => $flag_id,
                ]);
        }

        Schema::table('character_fitting_items', function (Blueprint $table) {

            $table->integer('flag')->change();

        });
    }
}
