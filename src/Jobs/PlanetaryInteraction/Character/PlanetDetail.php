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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Character;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanet;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetContent;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetExtractor;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetFactory;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetHead;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetLink;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetPin;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetRoute;
use Seat\Eveapi\Models\PlanetaryInteraction\CharacterPlanetRouteWaypoint;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class PlanetDetail.
 * @package Seat\Eveapi\Jobs\PlanetaryInteraction\Character
 */
class PlanetDetail extends AbstractAuthCharacterJob
{

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/planets/{planet_id}/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var string
     */
    protected $scope = 'esi-planets.manage_planets.v1';

    /**
     * @var array
     */
    protected $tags = ['pi', 'detail'];

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_pins;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_factories;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_extractors;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_heads;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_links;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_waypoints;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_routes;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $planet_contents;

    /**
     * PlanetDetail constructor.
     *
     * @param RefreshToken $token
     */
    public function __construct(RefreshToken $token)
    {

        $this->resetCollections();

        parent::__construct($token);
    }

    /**
     * Resets the collections used for cleanup routines.
     */
    private function resetCollections()
    {

        $this->planet_pins = collect();
        $this->planet_factories = collect();
        $this->planet_extractors = collect();
        $this->planet_heads = collect();
        $this->planet_links = collect();
        $this->planet_waypoints = collect();
        $this->planet_routes = collect();
        $this->planet_contents = collect();
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        CharacterPlanet::where('character_id', $this->getCharacterId())->get()->each(function ($planet) {

            $planet_detail = $this->retrieve([
                'character_id' => $this->getCharacterId(),
                'planet_id'    => $planet->planet_id,
            ]);

            // seed database with pins
            collect($planet_detail->pins)->each(function ($pin) use ($planet) {

                CharacterPlanetPin::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'planet_id'    => $planet->planet_id,
                    'pin_id'       => $pin->pin_id,
                ])->fill([
                    'type_id'          => $pin->type_id,
                    'schematic_id'     => $pin->schematic_id ?? null,
                    'latitude'         => $pin->latitude,
                    'longitude'        => $pin->longitude,
                    'install_time'     => property_exists($pin, 'install_time') ?
                        carbon($pin->install_time) : null,
                    'expiry_time'      => property_exists($pin, 'expiry_time') ?
                        carbon($pin->expiry_time) : null,
                    'last_cycle_start' => property_exists($pin, 'last_cycle_start') ?
                        carbon($pin->last_cycle_start) : null,
                ])->save();

                // Collect data for the cleanup phase
                $this->planet_pins->push($pin->pin_id);

                if (property_exists($pin, 'factory_details')) {

                    CharacterPlanetFactory::firstOrNew([
                        'character_id' => $this->getCharacterId(),
                        'planet_id'    => $planet->planet_id,
                        'pin_id'       => $pin->pin_id,
                    ])->fill([
                        'schematic_id' => $pin->factory_details->schematic_id,
                    ])->save();

                    // seeding garbage collector
                    $this->planet_factories->push($pin->pin_id);
                }

                if (property_exists($pin, 'extractor_details')) {

                    CharacterPlanetExtractor::firstOrNew([
                        'character_id' => $this->getCharacterId(),
                        'planet_id'    => $planet->planet_id,
                        'pin_id'       => $pin->pin_id,
                    ])->fill([
                        'product_type_id' => $pin->extractor_details->product_type_id ?? null,
                        'cycle_time'      => $pin->extractor_details->cycle_time ?? null,
                        'head_radius'     => $pin->extractor_details->head_radius ?? null,
                        'qty_per_cycle'   => $pin->extractor_details->qty_per_cycle ?? null,
                    ])->save();

                    // Collect data for the cleanup phase
                    $this->planet_extractors->push($pin->pin_id);

                    collect($pin->extractor_details->heads)->each(function ($head) use ($planet, $pin) {

                        CharacterPlanetHead::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'extractor_id' => $pin->pin_id,
                            'head_id'      => $head->head_id,
                        ])->fill([
                            'latitude'  => $head->latitude,
                            'longitude' => $head->longitude,
                        ])->save();

                        // seeding garbage collector
                        $this->planet_heads->push([
                            'extractor_id' => $pin->pin_id,
                            'head_id'      => $head->head_id,
                        ]);
                    });
                }

                if (property_exists($pin, 'contents')) {

                    collect($pin->contents)->each(function ($content) use ($planet, $pin) {

                        CharacterPlanetContent::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'pin_id'       => $pin->pin_id,
                            'type_id'      => $content->type_id,
                        ])->fill([
                            'amount' => $content->amount,
                        ])->save();

                        // seeding garbage collector
                        $this->planet_contents->push([
                            'pin_id'  => $pin->pin_id,
                            'type_id' => $content->type_id,
                        ]);

                    });
                }
            });

            collect($planet_detail->links)->each(function ($link) use ($planet) {

                CharacterPlanetLink::firstOrNew([
                    'character_id'       => $this->getCharacterId(),
                    'planet_id'          => $planet->planet_id,
                    'source_pin_id'      => $link->source_pin_id,
                    'destination_pin_id' => $link->destination_pin_id,
                ])->fill([
                    'link_level' => $link->link_level,
                ])->save();

                // Collect data for the cleanup phase
                $this->planet_links->push([
                    'source_pin_id'      => $link->source_pin_id,
                    'destination_pin_id' => $link->destination_pin_id,
                ]);

            });

            collect($planet_detail->routes)->each(function ($route) use ($planet) {

                CharacterPlanetRoute::firstOrNew([
                    'character_id' => $this->getCharacterId(),
                    'planet_id'    => $planet->planet_id,
                    'route_id'     => $route->route_id,
                ])->fill([
                    'source_pin_id'      => $route->source_pin_id,
                    'destination_pin_id' => $route->destination_pin_id,
                    'content_type_id'    => $route->content_type_id,
                    'quantity'           => $route->quantity,
                ])->save();

                // seeding garbage collector
                $this->planet_routes->push($route->route_id);

                if (property_exists($route, 'waypoints')) {
                    collect($route->waypoints)->each(function ($waypoint) use ($planet, $route) {

                        CharacterPlanetRouteWaypoint::firstOrNew([
                            'character_id' => $this->getCharacterId(),
                            'planet_id'    => $planet->planet_id,
                            'route_id'     => $route->route_id,
                            'pin_id'       => $waypoint,
                        ])->save();

                        // seeding garbage collector
                        $this->planet_waypoints->push([
                            'route_id' => $route->route_id,
                            'pin_id'   => $waypoint,
                        ]);

                    });
                }
            });

            $this->planetCleanup($planet);
        });
    }

    /**
     * Performs a cleanup of any routes, links or contents
     * of a planet.
     *
     * @param $planet
     *
     * @throws \Exception
     */
    private function planetCleanup($planet)
    {

        // cleaning all waypoints
        $this->cleanRouteWaypoints($planet);

        // cleaning all routes
        $this->cleanRoutes($planet);

        // cleaning links
        $this->cleanLinks($planet);

        // cleaning contents
        $this->cleanContents($planet);

        // cleaning heads
        $this->cleanHeads($planet);

        // cleaning extractors
        $this->cleanExtractors($planet);

        // cleaning factories
        $this->cleanFactories($planet);

        // cleaning pins
        $this->cleanPins($planet);

        $this->resetCollections();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanRouteWaypoints($planet)
    {

        CharacterPlanetRouteWaypoint::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn(DB::raw('CONCAT(route_id, ":", pin_id)'), $this->planet_waypoints
                ->map(function ($item) {

                    return $item['route_id'] . ':' . $item['pin_id'];
                })->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanRoutes($planet)
    {

        CharacterPlanetRoute::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn('route_id', $this->planet_routes->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanLinks($planet)
    {

        CharacterPlanetLink::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn(DB::raw('CONCAT(source_pin_id, ":", destination_pin_id)'), $this->planet_links
                ->map(function ($item) {

                    return $item['source_pin_id'] . ':' . $item['destination_pin_id'];
                })->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanContents($planet)
    {

        CharacterPlanetContent::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn(DB::raw('CONCAT(pin_id, ":", type_id)'), $this->planet_contents
                ->map(function ($item) {

                    return $item['pin_id'] . ':' . $item['type_id'];
                })->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanHeads($planet)
    {

        CharacterPlanetHead::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn(DB::raw('CONCAT(extractor_id, ":", head_id)'), $this->planet_heads
                ->map(function ($item) {

                    return $item['extractor_id'] . ':' . $item['head_id'];
                })->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanExtractors($planet)
    {

        CharacterPlanetExtractor::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn('pin_id', $this->planet_extractors->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanFactories($planet)
    {

        CharacterPlanetFactory::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn('pin_id', $this->planet_factories->toArray())
            ->delete();
    }

    /**
     * @param $planet
     *
     * @throws \Exception
     */
    private function cleanPins($planet)
    {

        CharacterPlanetPin::where('character_id', $this->getCharacterId())
            ->where('planet_id', $planet->planet_id)
            ->whereNotIn('pin_id', $this->planet_pins->toArray())
            ->delete();
    }
}
