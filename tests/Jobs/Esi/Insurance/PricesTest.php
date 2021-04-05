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

namespace Seat\Eveapi\Tests\Jobs\Esi\Insurance;

use Illuminate\Support\Facades\DB;
use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\EsiScopeAccessDeniedException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\PermanentInvalidTokenException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Corporation\Members;
use Seat\Eveapi\Jobs\Fittings\Character\Fittings;
use Seat\Eveapi\Jobs\Fittings\Insurances;
use Seat\Eveapi\Models\Corporation\CorporationMember;
use Seat\Eveapi\Models\Fittings\CharacterFitting;
use Seat\Eveapi\Models\Fittings\CharacterFittingItem;
use Seat\Eveapi\Models\Fittings\Insurance;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\BaseTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Fittings\FittingResource;
use Seat\Eveapi\Tests\Resources\Esi\Insurance\InsuranceResource;

/**
 * Class PricesTest
 * @package Seat\Eveapi\Tests\Jobs\Esi\Insurance
 */
class PricesTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/insurance/prices.json'),
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
            file_get_contents(__DIR__ . '/../../../artifacts/insurance/prices.json'),
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
        $job = new Insurances();
        $job->handle();

        $insurances = Insurance::all();

        $data = json_encode(InsuranceResource::collection($insurances->groupBy('type_id')));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/insurance/prices.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        Insurance::create([
            'type_id' => 1,
            'name' => 'Basic',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        Insurance::create([
            'type_id' => 1,
            'name' => 'Standard',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        Insurance::create([
            'type_id' => 1,
            'name' => 'Bronze',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new Insurances();
        $job->handle();

        $insurances = Insurance::all();

        foreach ($insurances as $insurance)
            $this->assertEquals($insurance->created_at, $insurance->updated_at);

        $this->assertCount(3, $insurances);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v1/insurance/prices/', 'datasource=tranquility');

        Insurance::create([
            'type_id' => 1,
            'name' => 'Basic',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        Insurance::create([
            'type_id' => 1,
            'name' => 'Standard',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        Insurance::create([
            'type_id' => 1,
            'name' => 'Bronze',
            'cost' => 100.17,
            'payout' => 200.38,
        ]);

        $insurances = Insurance::all();
        $data = json_encode(InsuranceResource::collection($insurances->groupBy('type_id')));
        $this->assertCount(3, $insurances);
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/insurance/prices.json', $data);

        $job = new Insurances();
        $job->handle();

        $insurances = Insurance::all();

        $data = json_encode(InsuranceResource::collection($insurances->groupBy('type_id')));
        $this->assertCount(1, $insurances);
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/insurance/prices.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Insurances();
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $job = new Insurances();
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $job = new Insurances();
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Insurances();
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $job = new Insurances();
        $job->handle();
    }
}
