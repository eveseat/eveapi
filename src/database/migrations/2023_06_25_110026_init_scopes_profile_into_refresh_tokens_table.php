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

/**
 * Class InitScopesProfileIntoRefreshTokensTable.
 */
class InitScopesProfileIntoRefreshTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // load sso scopes profiles
        $profiles = collect(setting('sso_scopes', true) ?? [
            [
                'id' => 0,
                'name' => 'default',
                'default' => true,
                'scopes' => config('eveapi.scopes', []),
            ],
        ]);

        DB::table('refresh_tokens')
            ->orderBy('character_id')
            ->each(function ($refresh_token) use ($profiles) {
                $profile = $profiles->where('scopes', json_decode($refresh_token->scopes))->first();

                DB::table('refresh_tokens')
                    ->where('character_id', $refresh_token->character_id)
                    ->update([
                        'scopes_profile' => $profile->id ?? 0,
                    ]);
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
}
