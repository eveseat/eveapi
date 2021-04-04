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

namespace Seat\Eveapi\Tests\Jobs\Esi\Skills;

use Seat\Eseye\Containers\EsiResponse;
use Seat\Eseye\Exceptions\EsiScopeAccessDeniedException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\PermanentInvalidTokenException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
use Seat\Eveapi\Exception\UnavailableEveServersException;
use Seat\Eveapi\Jobs\Character\Medals;
use Seat\Eveapi\Jobs\Skills\Character\Skills;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterInfoSkill;
use Seat\Eveapi\Models\Character\CharacterMedal;
use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache;
use Seat\Eveapi\Tests\Mocks\Esi\EsiMockFetcher;
use Seat\Eveapi\Tests\BaseTestCase;
use Seat\Eveapi\Tests\Resources\Esi\Character\MedalResource;
use Seat\Eveapi\Tests\Resources\Esi\Skills\SkillsResource;

/**
 * Class SkillsTest.
 * @package Seat\Eveapi\Tests\Jobs\Esi\Skills
 */
class SkillsTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // prepare dummy responses
        $response_success = new EsiResponse(
            file_get_contents(__DIR__ . '/../../../artifacts/skills/skills.json'),
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
            file_get_contents(__DIR__ . '/../../../artifacts/skills/skills.json'),
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
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $character = CharacterInfo::create([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);

        $job = new Skills($token);
        $job->handle();

        $data = json_encode(SkillsResource::make($character));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/skills/skills.json', $data);
    }

    /**
     * @depends testHandleSuccess
     */
    public function testHandleNotModified()
    {
        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        CharacterInfo::create([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);

        CharacterInfoSkill::create([
            'character_id' => 180548812,
            'total_sp' => 15465444,
            'unallocated_sp' => 900,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 1,
            'skill_id'             => 1,
            'skillpoints_in_skill' => 165498,
            'trained_skill_level'  => 1,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 1,
            'skill_id'             => 2,
            'skillpoints_in_skill' => 10000,
            'trained_skill_level'  => 1,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 4,
            'skill_id'             => 3,
            'skillpoints_in_skill' => 574874941,
            'trained_skill_level'  => 3,
        ]);

        // sleep for 5 seconds so we burn cache entry and move to ETag flow
        sleep(5);

        $job = new Skills($token);
        $job->handle();

        $skills = CharacterSkill::all();
        $this->assertCount(3, $skills);

        foreach ($skills as $skill)
            $this->assertEquals($skill->created_at, $skill->updated_at);

        $info = CharacterInfoSkill::find(180548812);
        $this->assertEquals($info->created_at, $info->updated_at);
    }

    /**
     * @depends testHandleNotModified
     */
    public function testHandleUpdated()
    {
        // bypass cache control to force job to be processed
        EsiInMemoryCache::getInstance()->forget('/v4/characters/180548812/skills/', 'datasource=tranquility');

        $token = new RefreshToken([
            'character_id' => 180548812,
            'version' => RefreshToken::CURRENT_VERSION,
            'user_id' => 0,
            'refresh_token' => 'refresh',
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $character = CharacterInfo::create([
            'character_id' => 180548812,
            'ancestry_id' => 19,
            'birthday' => "2015-03-24T11:37:00Z",
            'bloodline_id' => 3,
            'gender' => 'male',
            'name' => 'CCP Bartender',
            'race_id' => 2,
            'title' => 'Original title',
        ]);

        CharacterInfoSkill::create([
            'character_id' => 180548812,
            'total_sp' => 15465444,
            'unallocated_sp' => 900,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 1,
            'skill_id'             => 1,
            'skillpoints_in_skill' => 165498,
            'trained_skill_level'  => 1,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 1,
            'skill_id'             => 2,
            'skillpoints_in_skill' => 10000,
            'trained_skill_level'  => 1,
        ]);

        CharacterSkill::create([
            'character_id'         => 180548812,
            'active_skill_level'   => 4,
            'skill_id'             => 3,
            'skillpoints_in_skill' => 574874941,
            'trained_skill_level'  => 3,
        ]);

        $data = json_encode(SkillsResource::make($character));
        $this->assertJsonStringNotEqualsJsonFile(__DIR__ . '/../../../artifacts/skills/skills.json', $data);

        $job = new Skills($token);
        $job->handle();

        $character->load('skills', 'skillpoints');
        $data = json_encode(SkillsResource::make($character));
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../../../artifacts/skills/skills.json', $data);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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
            'scopes' => ['esi-skills.read_skills.v1'],
            'expires_on' => carbon()->addHour(),
            'token' => 'token',
            'character_owner_hash' => '87qs9fs1df1sfd654s65d4fgf6s6d4f654q6sf4d6q4gf63qsfc143q464sf',
        ]);
        $token->save();

        $job = new Skills($token);
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

        $job = new Skills($token);
        $job->handle();
    }
}
