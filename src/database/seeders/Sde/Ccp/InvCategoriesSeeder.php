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

namespace Seat\Eveapi\Database\Seeders\Sde\Ccp;

use Illuminate\Database\Schema\Blueprint;
use Seat\Eveapi\Mapping\Sde\AbstractSdeMapping;
use Seat\Eveapi\Database\Seeders\Sde\AbstractSdeSeeder;
use Seat\Eveapi\Mapping\Sde\Ccp\InvCategoryMapping;
use Seat\Eveapi\Models\Sde\InvCategory;

class InvCategoriesSeeder extends AbstractSdeSeeder
{

    protected const FILENAME = "categories.jsonl";

    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    protected function getSdeTableDefinition(Blueprint $table): void
    {
        $table->integer('categoryID')->primary();
        $table->string('categoryName', 100)->nullable();
        $table->integer('iconID')->nullable();
        $table->boolean('published')->default(false);
    }

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractSdeMapping
     */
    protected function getMappingClass(): AbstractSdeMapping
    {
        return new InvCategoryMapping();
    }

    public function insert($arr)
    {
        // Replace 'YourModel' with the actual model class name
        return InvCategory::insert($arr);
    }
}
