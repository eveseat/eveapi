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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class RemoveKillmailAttackersSurrogateKey.
 */
class RemoveKillmailAttackersSurrogateKey extends Migration
{
    public function up()
    {
        Schema::table('killmail_attackers', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->string('attacker_hash')->after('id');
        });

        $count = DB::table('killmail_attackers')->count();

        $output = new ConsoleOutput();
        $progress = new ProgressBar($output, $count);

        DB::table('killmail_attackers')->get()->each(function ($row) use ($progress) {
            $hash = md5(serialize([
                $row->character_id,
                $row->corporation_id,
                $row->alliance_id,
                $row->faction_id,
            ]));

            DB::table('killmail_attackers')
                ->where('killmail_id', $row->killmail_id)
                ->where('character_id', $row->character_id)
                ->where('corporation_id', $row->corporation_id)
                ->where('alliance_id', $row->alliance_id)
                ->where('faction_id', $row->faction_id)
                ->update([
                    'attacker_hash' => $hash,
                ]);

            $progress->advance();
        });

        // remove duplicate entries using killmail_id and attacker hash as pivot
        DB::statement('DELETE a FROM killmail_attackers a INNER JOIN killmail_attackers b WHERE a.id > b.id AND a.killmail_id = b.killmail_id AND a.attacker_hash = b.attacker_hash');

        Schema::table('killmail_attackers', function (Blueprint $table) {
            $table->unique(['killmail_id', 'attacker_hash']);
        });

        $progress->finish();
        $output->writeln('');
    }

    public function down()
    {
        Schema::table('killmail_attackers', function (Blueprint $table) {
            $table->dropUnique(['killmail_id', 'attacker_hash']);
            $table->dropPrimary(['id']);

            $table->dropColumn('attacker_hash');
        });
    }
}
