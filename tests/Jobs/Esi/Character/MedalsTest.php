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
use Seat\Eseye\Exceptions\EsiScopeAccessDeniedException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\PermanentInvalidTokenException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Character\Medals;
use Seat\Eveapi\Models\Character\CharacterMedal;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\Jobs\Esi\JobEsiTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Character\MedalResource;

/**
 * Class MedalsTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Character
 */
class MedalsTest extends JobEsiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/character/medals.json'),
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
            file_get_contents(__DIR__ . '/../../../artifacts/character/medals.json'),
            [
                'ETag' => '2b163975d331cee0273f42391831a1b9d7ca53cec57c45d6e4631cdc',
                'Expires' => carbon()->addSeconds(5)->toRfc7231String(),
            ],
            carbon()->addSeconds(5)->toRfc7231String(),
            200
        );

        $response_invalid_token = new EsiResponse('{"error":"invalid_token: The refresh token is expired."}', [], carbon()->toRfc7231String(), 400);
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
        EsiMockFetcher::add($response_invalid_token); // http@400
        EsiMockFetcher::add($response_success_bis); // http@200
        EsiMockFetcher::add($response_not_modified); // http@304
        EsiMockFetcher::add($response_success); // http@200
    }

    public function testHandleSuccess()
    {
        $token = new RefreshToken([
            'character_id' => 90795931,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();

        $medals = CharacterMedal::all();

        $data = json_encode(MedalResource::collection($medals));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/medals.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        $token = new RefreshToken([
            'character_id' => 90795931,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 24460,
            'title'          => 'Medaille des 1 an de services',
            'description'    => 'Dédiée aux vieux.....',
            'corporation_id' => 144275616,
            'issuer_id'      => 341025466,
            'date'           => '2018-07-01T19:37:39Z',
            'reason'         => 'Old Man',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color": -1,"graphic": "Caldari.01_01","layer": 0,"part": 1},{"color": -15132388,"graphic": "Caldari.3_1","layer": 1,"part": 1},{"color": -8847360,"graphic": "Caldari.2_4","layer": 2,"part": 1},{"color": -596431,"graphic": "Caldari.01_02","layer": 3,"part": 1},{"color": -1,"graphic": "elements.13_1","layer": 0,"part": 2},{"color": -1,"graphic": "star.2_2","layer": 1,"part": 2}]'),
        ]);

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 44753,
            'title'          => 'Médaille des 2 ans de services',
            'description'    => 'Dédiée aux plus vieux que les vieux',
            'corporation_id' => 144275616,
            'issuer_id'      => 341025466,
            'date'           => '2019-07-01 19:37:53',
            'reason'         => 'Very Old Man',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color":-1,"graphic":"Caldari.01_01","layer":0,"part":1},{"color":-8847360,"graphic":"Caldari.2_4","layer":1,"part":1},{"color":-15132388,"graphic":"Caldari.3_1","layer":2,"part":1},{"color":-596431,"graphic":"Caldari.01_02","layer":3,"part":1},{"color":-1,"graphic":"elements.21_1","layer":0,"part":2},{"color":-1,"graphic":"elements.13_1","layer":1,"part":2},{"color":-1,"graphic":"star.2_2","layer":2,"part":2}]'),
        ]);

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 71835,
            'title'          => 'Médaille du gentlemen',
            'description'    => 'Décoration attribuée pour services rendus durant la guerre qui nous opposa aux gentlemens durant les premiers jours de juillets.<br>Surcouf en son temps résuma très bien ce genre de combats entre les français et les anglais:<br>Alors que Surcouf (corsaire français) avait capturé un navire anglais, son capitaine, attaché sur son propre navire s\'addresse à lui en ces termes: "Vous les français, vous vous battez pour l\'argent, tandis que nous, anglais, nous nous battons pour l\'honneur". Ce à quoi Surcouf, dans un anglais parfait, répondit "Nous nous battons toujours pour ce que nous n\'avons pas..."<br><br>',
            'corporation_id' => 144275616,
            'issuer_id'      => 412492115,
            'date'           => '2014-07-10 22:44:43',
            'reason'         => 'Pour un scoot redoutable et une présence incroyable',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color":-1,"graphic":"Caldari.1_1","layer":0,"part":1},{"color":-8847360,"graphic":"Caldari.2_4","layer":1,"part":1},{"color":-4254186,"graphic":"Caldari.2_1","layer":2,"part":1},{"color":-15191726,"graphic":"Caldari.3_1","layer":3,"part":1},{"color":-330271,"graphic":"Caldari.1_2","layer":4,"part":1},{"color":-1,"graphic":"elements.24_2","layer":0,"part":2},{"color":-1,"graphic":"elements.13_2","layer":1,"part":2},{"color":-1,"graphic":"elements.17_2","layer":2,"part":2},{"color":-1,"graphic":"star.3_2","layer":3,"part":2}]'),
        ]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new Medals($token);
        $job->handle();

        $medals = CharacterMedal::all();

        foreach ($medals as $medal)
            $this->assertEquals($medal->created_at, $medal->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v1/characters/90795931/medals/', 'datasource=tranquility');

        $token = new RefreshToken([
            'character_id' => 90795931,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 24460,
            'title'          => 'Medaille des 1 an de services',
            'description'    => 'Dédiée aux vieux.....',
            'corporation_id' => 144275616,
            'issuer_id'      => 341025466,
            'date'           => '2018-07-01T19:37:39Z',
            'reason'         => 'Old Man',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color": -1,"graphic": "Caldari.01_01","layer": 0,"part": 1},{"color": -15132388,"graphic": "Caldari.3_1","layer": 1,"part": 1},{"color": -8847360,"graphic": "Caldari.2_4","layer": 2,"part": 1},{"color": -596431,"graphic": "Caldari.01_02","layer": 3,"part": 1},{"color": -1,"graphic": "elements.13_1","layer": 0,"part": 2},{"color": -1,"graphic": "star.2_2","layer": 1,"part": 2}]'),
        ]);

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 44753,
            'title'          => 'Médaille des 2 ans de services',
            'description'    => 'Dédiée aux plus vieux que les vieux',
            'corporation_id' => 144275616,
            'issuer_id'      => 341025466,
            'date'           => '2019-07-01 19:37:53',
            'reason'         => 'Very Old Man',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color":-1,"graphic":"Caldari.01_01","layer":0,"part":1},{"color":-8847360,"graphic":"Caldari.2_4","layer":1,"part":1},{"color":-15132388,"graphic":"Caldari.3_1","layer":2,"part":1},{"color":-596431,"graphic":"Caldari.01_02","layer":3,"part":1},{"color":-1,"graphic":"elements.21_1","layer":0,"part":2},{"color":-1,"graphic":"elements.13_1","layer":1,"part":2},{"color":-1,"graphic":"star.2_2","layer":2,"part":2}]'),
        ]);

        CharacterMedal::create([
            'character_id'   => 90795931,
            'medal_id'       => 71835,
            'title'          => 'Médaille du gentlemen',
            'description'    => 'Décoration attribuée pour services rendus durant la guerre qui nous opposa aux gentlemens durant les premiers jours de juillets.<br>Surcouf en son temps résuma très bien ce genre de combats entre les français et les anglais:<br>Alors que Surcouf (corsaire français) avait capturé un navire anglais, son capitaine, attaché sur son propre navire s\'addresse à lui en ces termes: "Vous les français, vous vous battez pour l\'argent, tandis que nous, anglais, nous nous battons pour l\'honneur". Ce à quoi Surcouf, dans un anglais parfait, répondit "Nous nous battons toujours pour ce que nous n\'avons pas..."<br><br>',
            'corporation_id' => 144275616,
            'issuer_id'      => 412492115,
            'date'           => '2014-07-10 22:44:43',
            'reason'         => 'Pour un scoot redoutable et une présence incroyable',
            'status'         => 'private',
            'graphics'       => json_encode('[{"color":-1,"graphic":"Caldari.1_1","layer":0,"part":1},{"color":-8847360,"graphic":"Caldari.2_4","layer":1,"part":1},{"color":-4254186,"graphic":"Caldari.2_1","layer":2,"part":1},{"color":-15191726,"graphic":"Caldari.3_1","layer":3,"part":1},{"color":-330271,"graphic":"Caldari.1_2","layer":4,"part":1},{"color":-1,"graphic":"elements.24_2","layer":0,"part":2},{"color":-1,"graphic":"elements.13_2","layer":1,"part":2},{"color":-1,"graphic":"elements.17_2","layer":2,"part":2},{"color":-1,"graphic":"star.3_2","layer":3,"part":2}]'),
        ]);

        $medals = CharacterMedal::all();
        $data = json_encode(MedalResource::collection($medals));
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/character/medals.json', $data);

        $job = new Medals($token);
        $job->handle();

        $medals = CharacterMedal::all();
        $data = json_encode(MedalResource::collection($medals));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/character/medals.json', $data);
    }

    /**
     * @depends testHandleUpdated
     */
    public function testInvalidToken()
    {
        $this->expectException(PermanentInvalidTokenException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    /**
     * @depends testInvalidToken
     */
    public function testHandleNotFound()
    {
        $this->expectException(RequestFailedException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    /**
     * @depends testHandleNotFound
     */
    public function testHandleErrorLimited()
    {
        $this->expectException(RequestFailedException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    /**
     * @depends testHandleErrorLimited
     */
    public function testHandleInternalServerError()
    {
        $this->expectException(TemporaryEsiOutageException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    /**
     * @depends testHandleInternalServerError
     */
    public function testHandleServiceUnavailable()
    {
        $this->expectException(UnavailableEveServersException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    /**
     * @depends testHandleServiceUnavailable
     */
    public function testHandleGatewayTimeout()
    {
        $this->expectException(UnavailableEveServersException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-characters.read_medals.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }

    public function testInvalidScope()
    {
        $this->expectException(EsiScopeAccessDeniedException::class);

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => '',
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Medals($token);
        $job->handle();
    }
}
