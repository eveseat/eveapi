<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
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

namespace Seat\Eveapi\Tests\Jobs\Esi\Character;

use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Character\Affiliation;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Tests\Jobs\Esi\JobEsiTestCase;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\Resources\Esi\Character\AffiliationResource;

/**
 * Class AffiliationTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Character
 */
class AffiliationTest extends JobEsiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/character/affiliation.json'),
            [
                'Expires' => carbon()->addSeconds(5)->toRfc7231String(),
            ],
            carbon()->addSeconds(5)->toRfc7231String(),
            200
        );

        $response_not_found = new EsiResponse('', [], carbon()->toRfc7231String(), 404);
        $response_error_limited = new EsiResponse('', [], carbon()->toRfc7231String(), 420);
        $response_internal_server_error = new EsiResponse('', [], carbon()->toRfc7231String(), 500);
        $response_service_unavailable = new EsiResponse('{"error":"The datasource tranquility is temporarily unavailable"}', [], carbon()->toRfc7231String(), 503);
        $response_gateway_timeout = new EsiResponse('{"error":"Timeout contacting tranquility"}', [], carbon()->toRfc7231String(), 504);

        // seed mock fetcher with response stack
        EsiMockFetcher::add($response_gateway_timeout); // http@504
        EsiMockFetcher::add($response_service_unavailable); // http@503
        EsiMockFetcher::add($response_internal_server_error); // http@500
        EsiMockFetcher::add($response_error_limited); // http@420
        EsiMockFetcher::add($response_not_found); // http@404
        EsiMockFetcher::add($response_success); // http@200
        EsiMockFetcher::add($response_success); // http@200
    }

    public function testHandleSuccess()
    {
        $job = new Affiliation([95538921, 1477919642, 90795931, 96057938]);
        $job->handle();

        $affiliation = CharacterAffiliation::all();

        $data = json_encode(AffiliationResource::collection($affiliation));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/affiliation.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleUpdated()
    {
        $affiliation = new CharacterAffiliation([
            'character_id' => 90795931,
            'corporation_id' => 98413060,
        ]);
        $affiliation->save();

        $data = json_encode($affiliation);
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/character/affiliation.json', $data);

        $job = new Affiliation([90795931]);
        $job->handle();

        $affiliation = CharacterAffiliation::all();

        $data = json_encode(AffiliationResource::collection($affiliation));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/affiliation.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Affiliation([404]);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Affiliation([420]);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new Affiliation([500]);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Affiliation([503]);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Affiliation([504]);
        $job->handle();
    }
}
