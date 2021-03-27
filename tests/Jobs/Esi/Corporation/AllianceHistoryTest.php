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
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Tests\Jobs\Esi\Corporation;

use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Corporation\AllianceHistory;
use Seat\Eveapi\Models\Corporation\CorporationAllianceHistory;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\Jobs\Esi\JobEsiTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Corporation\AllianceHistoryResource;

/**
 * Class AllianceHistoryTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Corporation
 */
class AllianceHistoryTest extends JobEsiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/corporation/alliance_history.json'),
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

        $response_success_bis = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/corporation/alliance_history.json'),
            [
                'ETag' => '2b163975d331cee0273f42391831a1b9d7ca53cec57c45d6e4631cdc',
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
        EsiMockFetcher::add($response_success_bis); // http@200
        EsiMockFetcher::add($response_not_modified); // http@304
        EsiMockFetcher::add($response_success); // http@200
    }

    public function testHandleSuccess()
    {
        $job = new AllianceHistory(109299958);
        $job->handle();

        $alliance_history = CorporationAllianceHistory::all();

        $data = json_encode(AllianceHistoryResource::collection($alliance_history));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/corporation/alliance_history.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        CorporationAllianceHistory::create([
            'corporation_id' => 109299958,
            'alliance_id' => 99000006,
            'is_deleted' => false,
            'record_id' => 23,
            'start_date' => '2016-10-25T14:46:00Z',
        ]);

        CorporationAllianceHistory::create([
            'corporation_id' => 109299958,
            'record_id' => 1,
            'start_date' => '2015-07-06T20:00:00Z',
        ]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new AllianceHistory(109299958);
        $job->handle();

        $alliances_history = CorporationAllianceHistory::all();

        foreach ($alliances_history as $alliance_history)
            $this->assertEquals($alliance_history->created_at, $alliance_history->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v2/corporations/109299958/alliancehistory/', 'datasource=tranquility');

        CorporationAllianceHistory::create([
            'corporation_id' => 109299958,
            'alliance_id' => 99000006,
            'is_deleted' => false,
            'record_id' => 23,
            'start_date' => '2016-10-25T14:46:00Z',
        ]);

        CorporationAllianceHistory::create([
            'corporation_id' => 109299958,
            'record_id' => 1,
            'start_date' => '2015-07-06T20:00:00Z',
        ]);

        $alliances_history = CorporationAllianceHistory::all();

        $data = json_encode(AllianceHistoryResource::collection($alliances_history));
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/corporation/alliance_history.json', $data);

        $job = new AllianceHistory(109299958);
        $job->handle();

        $alliances_history = CorporationAllianceHistory::all();

        $data = json_encode(AllianceHistoryResource::collection($alliances_history));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/corporation/alliance_history.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new AllianceHistory(404);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new AllianceHistory(420);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new AllianceHistory(500);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new AllianceHistory(503);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new AllianceHistory(504);
        $job->handle();
    }
}
