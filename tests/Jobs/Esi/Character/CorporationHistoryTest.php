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
use Seat\Eveapi\Jobs\Character\CorporationHistory;
use Seat\Eveapi\Models\Character\CharacterCorporationHistory;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\Jobs\Esi\JobEsiTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Character\CorporationHistoryResource;

/**
 * Class CorporationHistoryTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Character
 */
class CorporationHistoryTest extends JobEsiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/characters/corporation_history.json'),
            [
                'ETag' => '2b163975d331cee0273f42391831a1b9d7ca53cec57c45d6e4631cdc',
                'Expires' => carbon()->addSeconds(5)->toRfc7231String(),
            ],
            carbon()->addSeconds(5)->toRfc7231String(),
            200
        );

        $response_not_modified = new EsiResponse(
            '',
            [
                'ETag' => '2b163975d331cee0273f42391831a1b9d7ca53cec57c45d6e4631cdc',
                'Expires' => carbon()->addHour()->toRfc7231String(),
            ],
            carbon()->addHour()->toRfc7231String(),
            304
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
        EsiMockFetcher::add($response_not_modified); // http@304
        EsiMockFetcher::add($response_success); // http@200
    }

    public function testHandleSuccess()
    {
        $job = new CorporationHistory(90795931);
        $job->handle();

        $corporations = CharacterCorporationHistory::all();

        $data = json_encode(CorporationHistoryResource::collection($corporations));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/characters/corporation_history.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        CharacterCorporationHistory::create([
            'character_id'   => 90795931,
            'corporation_id' => 98413060,
            'record_id'      => 303,
            'is_deleted'     => false,
            'start_date'     => '2018-08-24T17:42:00Z',
        ]);

        CharacterCorporationHistory::create([
            'character_id'   => 90795931,
            'corporation_id' => 98456198,
            'record_id'      => 1009,
            'is_deleted'     => false,
            'start_date'     => '2017-02-01T20:50:00Z',
        ]);

        CharacterCorporationHistory::create([
            'character_id'   => 90795931,
            'corporation_id' => 1000014,
            'record_id'      => 198797,
            'is_deleted'     => false,
            'start_date'     => '2017-02-01T20:50:00Z',
        ]);

        CharacterCorporationHistory::create([
            'character_id'   => 90795931,
            'corporation_id' => 98451304,
            'record_id'      => 917,
            'is_deleted'     => true,
            'start_date'     => '2016-08-07T13:45:00Z',
        ]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new CorporationHistory(90795931);
        $job->handle();

        $corporations = CharacterCorporationHistory::all();

        foreach ($corporations as $corporation)
            $this->assertEquals($corporation->created_at, $corporation->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new CorporationHistory(404);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new CorporationHistory(420);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new CorporationHistory(500);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new CorporationHistory(503);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new CorporationHistory(504);
        $job->handle();
    }
}
