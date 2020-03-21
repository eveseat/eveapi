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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ConvertMailRecipientsTable.
 */
class ConvertMailRecipientsTable extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $count = DB::table('mig_mail_recipients')
            ->distinct()
            ->count();

        $output = new ConsoleOutput();
        $progress = new ProgressBar($output, $count);

        // seed mail recipients table using migration table
        DB::table('mig_mail_recipients')
            ->select('mail_id', 'recipient_id', 'is_read', 'labels')
            ->orderBy('mail_id')
            ->distinct()
            ->each(function ($entry) use ($progress) {
                DB::table('mail_recipients')
                    ->updateOrInsert(
                        [
                            'mail_id'        => $entry->mail_id,
                            'recipient_id'   => $entry->recipient_id,
                            'recipient_type' => 'character',
                        ],
                        [
                            'is_read' => $entry->is_read,
                            'labels'  => $entry->labels,
                        ]);

                $progress->advance();
            });

        Schema::dropIfExists('mig_mail_recipients');

        Schema::enableForeignKeyConstraints();

        $progress->finish();
        $output->writeln('');
    }

    public function down()
    {

    }
}
