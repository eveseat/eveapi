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

namespace Seat\Eveapi\Tests\Jobs\Esi\Alliances;

use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Alliances\Members;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Universe\UniverseName;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\Jobs\Esi\JobEsiTestCase;

/**
 * Class CorporationsTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Alliances
 */
class CorporationsTest extends JobEsiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $response_names = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/universe/names.json'),
            [
                'Expires' => carbon()->addSeconds(5)->toRfc7231String(),
            ],
            carbon()->addSeconds(5)->toRfc7231String(),
            200
        );

        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/alliances/corporations.json'),
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
            file_get_contents(__DIR__ . '/../../../artifacts/alliances/corporations.json'),
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
        EsiMockFetcher::add($response_names);
        EsiMockFetcher::add($response_success_bis); // http@200
        EsiMockFetcher::add($response_not_modified); // http@304
        EsiMockFetcher::add($response_names);
        EsiMockFetcher::add($response_success); // http@200
    }

    public function testHandleSuccess()
    {
        $alliance = Alliance::create([
            'alliance_id'             => 99000137,
            'creator_corporation_id'  => 157983505,
            'creator_id'              => 211292683,
            'executor_corporation_id' => 157983505,
            'date_founded'            => '2010-12-16T02:35:00Z',
            'name'                    => 'Renaissance Federation',
            'ticker'                  => 'RF',
        ]);

        $job = new Members(99000137);
        $job->handle();

        $alliance->load('members');
        $data = json_encode($alliance->members->sortBy('entity_id')->pluck('entity_id')->toArray());
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/alliances/corporations.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        $alliance = Alliance::create([
            'alliance_id'             => 99000137,
            'creator_corporation_id'  => 157983505,
            'creator_id'              => 211292683,
            'executor_corporation_id' => 157983505,
            'date_founded'            => '2010-12-16T02:35:00Z',
            'name'                    => 'Renaissance Federation',
            'ticker'                  => 'RF',
        ]);

        $first_corporation = UniverseName::create([
            'entity_id' => 65465464684,
            'name' => 'Test Corporation 1',
            'category' => 'corporation',
        ]);

        $second_corporation = UniverseName::create([
            'entity_id' => 5454131657,
            'name' => 'Test Corporation 2',
            'category' => 'corporation',
        ]);

        $third_corporation = UniverseName::create([
            'entity_id' => 1021546564,
            'name' => 'Test Corporation 3',
            'category' => 'corporation',
        ]);

        $alliance->members()->attach([$first_corporation->entity_id, $second_corporation->entity_id, $third_corporation->entity_id]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new Members(99000137);
        $job->handle();

        $alliance = Alliance::find(99000137);

        $this->assertCount(3, $alliance->members);
        foreach ($alliance->members as $member)
            $this->assertEquals($member->created_at, $member->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v1/alliances/99000137/corporations/', 'datasource=tranquility');

        $alliance = Alliance::create([
            'alliance_id'             => 99000137,
            'creator_corporation_id'  => 157983505,
            'creator_id'              => 211292683,
            'executor_corporation_id' => 157983505,
            'date_founded'            => '2010-12-16T02:35:00Z',
            'name'                    => 'Renaissance Federation',
            'ticker'                  => 'RF',
        ]);

        $first_corporation = UniverseName::create([
            'entity_id' => 65465464684,
            'name' => 'Test Corporation 1',
            'category' => 'corporation',
        ]);

        $second_corporation = UniverseName::create([
            'entity_id' => 5454131657,
            'name' => 'Test Corporation 2',
            'category' => 'corporation',
        ]);

        $third_corporation = UniverseName::create([
            'entity_id' => 1021546564,
            'name' => 'Test Corporation 3',
            'category' => 'corporation',
        ]);

        $alliance->members()->attach([$first_corporation->entity_id, $second_corporation->entity_id, $third_corporation->entity_id]);

        $alliance = Alliance::find(99000137);

        $data = json_encode($alliance->members->sortBy('entity_id')->pluck('entity_id')->toArray());
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/alliances/corporations.json', $data);

        $job = new Members(99000137);
        $job->handle();

        $alliance->load('members');
        $data = json_encode($alliance->members->sortBy('entity_id')->pluck('entity_id')->toArray());
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/alliances/corporations.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Members(404);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Members(420);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new Members(500);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Members(503);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Members(504);
        $job->handle();
    }
}
