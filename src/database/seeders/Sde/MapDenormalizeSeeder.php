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

namespace Seat\Eveapi\Database\Seeders\Sde;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping;
use Seat\Eveapi\Mapping\Sde\MapDenormalizeMapping;
use Seat\Eveapi\Models\Sde\MapDenormalize;

class MapDenormalizeSeeder extends AbstractSdeSeeder
{
    /**
     * Define seeder related SDE table structure.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @return void
     */
    protected function getSdeTableDefinition(Blueprint $table): void
    {
        $table->integer('itemID')->primary();
        $table->integer('typeID')->nullable();
        $table->integer('groupID')->nullable();
        $table->integer('solarSystemID')->nullable();
        $table->integer('constellationID')->nullable();
        $table->integer('regionID')->nullable();
        $table->integer('orbitID')->nullable();
        $table->double('x')->nullable();
        $table->double('y')->nullable();
        $table->double('z')->nullable();
        $table->double('radius')->nullable();
        $table->string('itemName', 100)->nullable();
        $table->double('security')->nullable();
        $table->integer('celestialIndex')->nullable();
        $table->integer('orbitIndex')->nullable();
    }

    /**
     * The mapping instance which must be used to seed table with SDE dump.
     *
     * @return \Seat\Eveapi\Mapping\Sde\AbstractFuzzworkMapping
     */
    protected function getMappingClass(): AbstractFuzzworkMapping
    {
        return new MapDenormalizeMapping();
    }

    /**
     * Determine actions which have to be executed after the seeding process.
     *
     * @return void
     */
    protected function after(): void
    {
        $this->explodeMap();
    }

    /**
     * Explode mapDenormalize table into celestial sub-tables.
     */
    private function explodeMap()
    {
        // extract regions
        $this->seedRegionsTable();

        // extract constellations
        $this->seedConstellationsTable();

        // extract solar systems
        $this->seedSolarSystemsTable();

        // extract stars
        $this->seedStarsTable();

        // extract planets
        $this->seedPlanetsTable();

        // extract moons
        $this->seedMoonsTable();
    }

    /**
     * Extract regions from SDE table.
     *
     * @return void
     */
    private function seedRegionsTable()
    {
        DB::table('regions')->truncate();
        DB::table('regions')
            ->insertUsing([
                'region_id', 'name',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::REGION)
                ->select('itemID', 'itemName'));
    }

    /**
     * Extract constellations from SDE table.
     *
     * @return void
     */
    private function seedConstellationsTable()
    {
        DB::table('constellations')->truncate();
        DB::table('constellations')
            ->insertUsing([
                'constellation_id', 'region_id', 'name',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::CONSTELLATION)
                ->select('itemID', 'regionID', 'itemName'));
    }

    /**
     * Extract systems from SDE table.
     *
     * @return void
     */
    private function seedSolarSystemsTable()
    {
        DB::table('solar_systems')->truncate();
        DB::table('solar_systems')
            ->insertUsing([
                'system_id', 'constellation_id', 'region_id', 'name', 'security',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::SYSTEM)
                ->select('itemID', 'constellationID', 'regionID', 'itemName', 'security'));
    }

    /**
     * Extract stars from SDE table.
     *
     * @return void
     */
    private function seedStarsTable()
    {
        DB::table('stars')->truncate();
        DB::table('stars')
            ->insertUsing([
                'star_id', 'system_id', 'constellation_id', 'region_id', 'name', 'type_id',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::SUN)
                ->select('itemID', 'solarSystemID', 'constellationID', 'regionID', 'itemName', 'typeID'));
    }

    /**
     * Extract planets from SDE table.
     *
     * @return void
     */
    private function seedPlanetsTable()
    {
        DB::table('planets')->truncate();
        DB::table('planets')
            ->insertUsing([
                'planet_id', 'system_id', 'constellation_id', 'region_id', 'name', 'type_id',
                'x', 'y', 'z', 'radius', 'celestial_index',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::PLANET)
                ->select('itemID', 'solarSystemID', 'constellationID', 'regionID', 'itemName', 'typeID',
                    'x', 'y', 'z', 'radius', 'celestialIndex'));
    }

    /**
     * Extract moons from SDE table.
     *
     * @return void
     */
    private function seedMoonsTable()
    {
        DB::table('moons')->truncate();
        DB::table('moons')
            ->insertUsing([
                'moon_id', 'planet_id', 'system_id', 'constellation_id', 'region_id', 'name', 'type_id',
                'x', 'y', 'z', 'radius', 'celestial_index', 'orbit_index',
            ], DB::table((new MapDenormalize())->getTable())->where('groupID', MapDenormalize::MOON)
                ->select('itemID', 'orbitID', 'solarSystemID', 'constellationID', 'regionID', 'itemName', 'typeID',
                    'x', 'y', 'z', 'radius', 'celestialIndex', 'orbitIndex'));
    }
}
