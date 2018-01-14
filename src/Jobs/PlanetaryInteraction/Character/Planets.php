<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Character;


use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanet;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetContent;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetExtractor;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetFactory;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetHead;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetLink;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetPin;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetRoute;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetRouteWaypoint;

/**
 * Class Planet
 * @package Seat\Eveapi\Jobs\PlanetaryInteraction\Character
 */
class Planets extends EsiBase
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/planets/';

    /**
     * @var int
     */
    protected $version = 'v1';

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {

        $planets = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        collect($planets)->each(function ($planet) {

            CharacterPlanet::firstOrNew([
                'character_id'    => $this->getCharacterId(),
                'solar_system_id' => $planet->solar_system_id,
                'planet_id'       => $planet->planet_id,
            ])->fill([
                'upgrade_level'   => $planet->upgrade_level,
                'num_pins'        => $planet->num_pins,
                'last_update'     => carbon($planet->last_update),
                'planet_type'     => $planet->planet_type,
            ])->save();

            //
            // Fetch planet detailed information
            //

            $planet_detail = $this->eseye()
                 ->setVersion('v3')
                 ->invoke('get', '/characters/{character_id}/planets/{planet_id}', [
                    'character_id' => $this->getCharacterId(),
                    'planet_id'    => $planet->planet_id,
                 ]);

            // seed database with pins
            collect($planet_detail->pins)->each(function($pin) use ($planet) {

                CharacterPlanetPin::firstOrNew([
                    'character_id'     => $this->getCharacterId(),
                    'planet_id'        => $planet->planet_id,
                    'pin_id'           => $pin->pin_id,
                ])->fill([
                    'type_id'          => $pin->type_id,
                    'schematic_id'     => property_exists($pin, 'schematic_id') ? $pin->schematic_id : null,
                    'latitude'         => $pin->latitude,
                    'longitude'        => $pin->longitude,
                    'install_time'     => property_exists($pin, 'install_time') ? carbon($pin->install_time) : null,
                    'expiry_time'      => property_exists($pin, 'expiry_time') ? carbon($pin->expiry_time) : null,
                    'last_cycle_start' => property_exists($pin, 'last_cycle_start') ? carbon($pin->last_cycle_start) : null,
                ])->save();

                if (property_exists($pin, 'factory_details'))
                    CharacterPlanetFactory::firstOrNew([
                        'character_id' => $this->getCharacterId(),
                        'planet_id'    => $planet->planet_id,
                        'pin_id'       => $pin->pin_id,
                    ])->fill([
                        'schematic_id' => $pin->factory_details->schematic_id,
                    ])->save();

                if (property_exists($pin, 'extractor_details')) {
                    CharacterPlanetExtractor::firstOrNew([
                        'character_id' => $this->getCharacterId(),
                        'planet_id'    => $planet->planet_id,
                        'pin_id'       => $pin->pin_id,
                    ])->fill( [
                        'product_type_id' => property_exists( $pin->extractor_details, 'product_type_id' ) ?
                            $pin->extractor_details->product_type_id : null,
                        'cycle_time'      => property_exists( $pin->extractor_details, 'cycle_time' ) ?
                            $pin->extractor_details->cycle_time : null,
                        'head_radius'     => property_exists( $pin->extractor_details, 'head_radius' ) ?
                            $pin->extractor_details->head_radius : null,
                        'qty_per_cycle'   => property_exists( $pin->extractor_details, 'qty_per_cycle' ) ?
                            $pin->extractor_details->qty_per_cycle : null,
                    ])->save();

                    collect($pin->extractor_details->heads)->each(function($head) use ($planet, $pin){
                        CharacterPlanetHead::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'extractor_id' => $pin->pin_id,
                            'head_id'      => $head->head_id,
                        ])->fill([
                            'latitude'     => $head->latitude,
                            'longitude'    => $head->longitude,
                        ])->save();
                    });
                }

                if (property_exists($pin, 'contents'))
                    collect($pin->contents)->each(function($content) use ($planet, $pin){

                        CharacterPlanetContent::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'pin_id'       => $pin->pin_id,
                            'type_id'      => $content->type_id
                        ])->fill([
                            'amount'       => $content->amount,
                        ])->save();

                    });

            });

            collect($planet_detail->links)->each(function($link) use ($planet) {

                CharacterPlanetLink::firstOrNew([
                    'character_id'       => $this->getCharacterId(),
                    'planet_id'          => $planet->planet_id,
                    'source_pin_id'      => $link->source_pin_id,
                    'destination_pin_id' => $link->destination_pin_id,
                ])->fill([
                    'link_level'         => $link->link_level,
                ])->save();

            });

            collect($planet_detail->routes)->each(function($route) use ($planet) {

                CharacterPlanetRoute::firstOrNew( [
                    'character_id'       => $this->getCharacterId(),
                    'planet_id'          => $planet->planet_id,
                    'route_id'           => $route->route_id,
                ])->fill([
                    'source_pin_id'      => $route->source_pin_id,
                    'destination_pin_id' => $route->destination_pin_id,
                    'content_type_id'    => $route->content_type_id,
                    'quantity'           => $route->quantity
                ])->save();

                if (property_exists($route, 'waypoints')) {
                    collect($route->waypoints)->each(function ($waypoint) use ($planet, $route) {

                        CharacterPlanetRouteWaypoint::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'route_id'     => $route->route_id,
                            'pin_id'       => $waypoint
                        ])->save();

                    });
                }

            });

        });

        // Cleanup solar system ids that have removed planets
        collect($planets)->unique('solar_system_id')
            ->pluck('solar_system_id')->each(function ($solar_system_id) use ($planets) {

                CharacterPlanet::where('character_id', $this->getCharacterId())
                    ->where('solar_system_id', $solar_system_id)
                    ->whereNotIn('planet_id', collect($planets)
                        ->pluck('planet_id')->flatten()->all())
                    ->delete();
            });

        // Remove empty solarsystem ids
        CharacterPlanet::where('character_id', $this->getCharacterId())
            ->whereNotIn('solar_system_id', collect($planets)
                ->pluck('solar_system_id')->flatten()->all())
            ->delete();
    }
}
