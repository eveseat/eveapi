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
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Extends https://github.com/eveseat/eveapi/pull/426

        $remove_scopes = [
            'esi-characterstats.read.v1',
        ];

        // Fix existing tokens
        DB::table('refresh_tokens')
            ->orderBy('character_id')
            ->lazy()->each(function ($refresh_token) use ($remove_scopes) {
                $scopes = json_decode($refresh_token->scopes);
                $action_taken = false;
                foreach($remove_scopes as $rs) {
                    foreach(array_keys($scopes, $rs, true) as $key) {
                        unset($scopes[$key]);
                        $action_taken = true;
                    }
                }
                if ($action_taken) {
                    $scopes = array_values($scopes);
                    DB::table('refresh_tokens')
                        ->where('character_id', $refresh_token->character_id)
                        ->update([
                            'scopes' => json_encode($scopes),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
