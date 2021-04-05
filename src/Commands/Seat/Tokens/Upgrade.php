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

namespace Seat\Eveapi\Commands\Seat\Tokens;

use GuzzleHttp\Client; // to be replaced by Laravel Http facade - https://laravel.com/docs/8.x/http-client
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class Upgrade.
 * @package Seat\Eveapi\Commands\Seat\Tokens
 */
class Upgrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:tokens:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade all tokens to latest sso version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->line('SeAT Token Upgrader');

        $client = new Client([
            'timeout' => 30,
        ]);
        $authsite = 'https://login.eveonline.com/v2/oauth/token';

        $errors = 0;
        $perm = 0;
        $success = 0;

        $count = DB::table('refresh_tokens')
            ->whereNull('deleted_at')
            ->count();
        $progress = $this->output->createProgressBar($count);

        RefreshToken::chunk(100, function ($tokens) use ($client, &$errors, &$success, &$perm, $authsite, $progress) {
                foreach ($tokens as $token){
                    if ($token->version == RefreshToken::CURRENT_VERSION){
                        continue;
                    }
                    try{
                        $token_headers = [
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode(config('esi.eseye_client_id') . ':' . config('esi.eseye_client_secret')),
                                'User-Agent' => 'Eve SeAT SSO v2 Migrator. Contact eveseat slack or github. https://github.com/eveseat/seat',
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Host' => 'login.eveonline.com',
                            ],
                            'form_params' => [
                                'grant_type' => 'refresh_token',
                                'refresh_token' => $token->refresh_token,
                            ],
                        ];

                        $result = $client->post($authsite, $token_headers);
                        $resp = json_decode($result->getBody());
                        $expires_new = carbon()::createFromTimestamp(time() + $resp->expires_in);

                        $token->token = $resp->access_token;
                        $token->refresh_token = $resp->refresh_token;
                        $token->expires_on = $expires_new;
                        $token->version = RefreshToken::CURRENT_VERSION;

                        $token->save();

                        $success += 1;

                    } catch (RequestException $e) {
                        logger()->error('Error Migrating Refresh Token', [
                            'Character ID'   => $token->character_id,
                            'Message' => $e->getMessage(),
                            'Body' => (string) $e->getResponse()->getBody(),
                            'Headers' => $e->getResponse()->getHeaders(),
                        ]);

                        if (strpos((string) $e->getResponse()->getBody(), 'invalid_grant') !== false) {
                            $perm += 1;
                            $token->delete();
                        } else{
                            $errors += 1;
                        }
                    }
                    $progress->advance();
                }
            });

            $progress->finish();
            $this->line('');

            $this->info('SeAT SSO Token Migration Complete!');
            $this->info('Success: ' . $success);
            $this->warn('Temp Fail: ' . $errors);
            $this->error('Perm Fail: ' . $perm);
    }
}
