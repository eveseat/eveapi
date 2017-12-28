<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
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

namespace Seat\Eveapi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;
use Seat\Eveapi\Exception\MissingTokenException;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class EsiBase
 * @package Seat\Eveapi\Jobs
 */
abstract class EsiBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var mixed
     */
    protected $client;

    /**
     * @var int
     */
    protected $character_id;

    /**
     * @var
     */
    protected $token;

    /**
     * Create a new job instance.
     *
     * @param int $character_id
     */
    public function __construct(int $character_id)
    {

        $this->client = app('esi-client');
        $this->character_id = $character_id;
    }

    /**
     * @return \Seat\Eseye\Eseye
     * @throws \Illuminate\Container\EntryNotFoundException
     * @throws \Throwable
     */
    public function getCharacterClient(): Eseye
    {

        $this->token = RefreshToken::find($this->character_id);

        throw_if(is_null($this->token),
            MissingTokenException::class,
            'Character ' . $this->character_id . ' has no recorded ESI tokens.');

        return $this->client = $this->client->get(new EsiAuthentication([
            'refresh_token' => $this->token->refresh_token,
            'access_token'  => $this->token->token,
            'token_expires' => $this->token->expires_on,
            'scopes'        => $this->token->scopes,
        ]));
    }

    /**
     * Before we finish the job, update the access_token
     * last used in the job, along with the expiry time.
     */
    public function __destruct()
    {

        tap($this->token, function ($token) {

            $last_auth = $this->client->getAuthentication();

            $token->token = $last_auth->access_token ?? '-';
            $token->expires_on = $last_auth->token_expires;

            $token->save();
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle();

}
