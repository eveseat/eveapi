<?php

namespace Seat\Eveapi\Jobs\Token;

use Illuminate\Contracts\Container\BindingResolutionException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;

class RefreshAccessToken extends AbstractJob
{
    /**
     * @var array
     */
    protected $tags = ['character', 'token'];

    /**
     * @var \Seat\Eveapi\Models\RefreshToken
     */
    protected $token;

    /**
     * @var \Seat\Services\Contracts\EsiClient
     */
    protected EsiClient $esi;


    /**
     * @param RefreshToken $token
     * @throws BindingResolutionException
     */
    public function __construct(RefreshToken $token)
    {
        $this->token = $token;
        $this->esi = app()->make(EsiClient::class);
    }


    /**
     * @return void
     */
    public function handle()
    {
        // normally the retrieve function passes the token down the esi stack, but we don't use retrieve
        $this->esi->setAuthentication($this->token);

        try {
            // get or renew access token
            $this->esi->getValidAccessToken();
        } catch (RequestFailedException $e) {

        }

        // save the new access token. the following logic is extracted from EsiBase
        $this->token = $this->token->fresh(); // since the model might have been in the queue for a while, amke sure to get the latest info
        $last_auth = $this->esi->getAuthentication(); // extract the access token info from eseye

        if (! empty($last_auth->getRefreshToken()))
            $this->token->refresh_token = $last_auth->getRefreshToken();

        $this->token->token = $last_auth->getAccessToken() ?? '-';
        $this->token->expires_on = $last_auth->getExpiresOn();

        $this->token->save();
    }
}