<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
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

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Seat\Eveapi\Models\RefreshToken;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use GuzzleHttp\Client;

/**
 * Class RenameCharacterAgentResearchesIntoCharacterAgentResearch.
 */
class UpgradeRefreshTokens extends Migration
{
    public function up()
    {
        $client = new Client([
            'timeout' => 30,
        ]);
        $authsite = 'https://login.eveonline.com/v2/oauth/token';

        $errors = 0;
        $success = 0;

        $count = DB::table('refresh_tokens')->count();
        $output = new ConsoleOutput();
        $progress = new ProgressBar($output, $count);

        RefreshToken::chunk(100, function ($tokens) use ($client, &$errors, &$success, $authsite, $progress){
            foreach ($tokens as $token){
                try{
                    $token_headers = [
                        'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(env('EVE_CLIENT_ID') . ':' . env('EVE_CLIENT_SECRET')),
                        'User-Agent' => 'Eve SeAT SSO v2 Migrator. Contact Crypta Electrica',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Host' => 'login.eveonline.com',
                        ],
                        'form_params' => [
                        // 'client_id' => env('EVE_CLIENT_ID'),
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $token->refresh_token,
                        ]
                    ];
    
                    $result = $client->post($authsite, $token_headers);
                    $resp = json_decode($result->getBody());
                    $expires_new = Carbon::createFromTimestamp(time() + $resp->expires_in);
    
                    $token->token = $resp->access_token;
                    $token->refresh_token = $resp->refresh_token;
                    $token->expires_on = $expires_new;
    
                    $token->save();
    
                    $success += 1;
    
                } catch (RequestException $e) {
                    logger()->error('Error Migrating Refresh Token', [
                        'Character ID'   => $token->character_id,
                        'Message' => $e->getMessage(),
                        'Body' => (string) $e->getResponse()->getBody(),
                        'Headers' => $e->getResponse()->getHeaders(),
                    ]);
                    $errors += 1;
                };
                $progress->advance();
            }
        });

        $progress->finish();
        $output->writeln('');
        
        logger()->error('SeAT SSO Token Migration Complete!', [
            'Migrated' => $success,
            'Failures' => $errors,
        ]);

    }

    public function down()
    {
        // There is no downgrade possible from here. Not sure, should I throw an exception or just let it go?
    }


}

