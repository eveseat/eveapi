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
use Seat\Eveapi\Jobs\Character\Info;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\BaseTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Character\InfoResource;

/**
 * Class InfoTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Character
 */
class InfoTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/character/info.json'),
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
            file_get_contents(__DIR__ . '/../../../artifacts/character/info.json'),
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
        $job = new Info(180548812);
        $job->handle();

        $character = CharacterInfo::find(180548812);

        // add extra field to be ESI structure compliant
        $character->corporation_id = 109299958;

        $data = json_encode(new InfoResource($character));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/info.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        $character = new CharacterInfo([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);
        $character->save();

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new Info(180548812);
        $job->handle();

        $character = CharacterInfo::find(180548812);

        $this->assertEquals($character->created_at, $character->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v4/characters/180548812/', 'datasource=tranquility');

        $character = new CharacterInfo([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);
        $character->save();

        $data = json_encode(new InfoResource($character));
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/character/info.json', $data);

        $job = new Info(180548812);
        $job->handle();

        $character = CharacterInfo::find(180548812);

        // add extra field to be ESI structure compliant
        $character->corporation_id = 109299958;

        $data = json_encode(new InfoResource($character));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/info.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Info(404);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Info(420);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new Info(500);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Info(503);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Info(504);
        $job->handle();
    }
}
