<?php

namespace App\Services;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\CryptKey;
use DateInterval;

class OAuthServerService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function make()
    {
        $server = new AuthorizationServer(
            app(\App\Repositories\ClientRepository::class),
            app(\App\Repositories\AccessTokenRepository::class),
            app(\App\Repositories\ScopeRepository::class),
            new CryptKey(storage_path('oauth/private.key'), null, false),
            config('app.key')
        );

        $authCodeGrant = new AuthCodeGrant(
            app(\App\Repositories\AuthCodeRepository::class),
            app(\App\Repositories\RefreshTokenRepository::class),
            new DateInterval('PT10M') // auth code valid 10 minutes
        );

        $authCodeGrant->setRefreshTokenTTL(new DateInterval('P1M'));

        $server->enableGrantType(
            $authCodeGrant,
            new DateInterval('PT1H') // access token valid 1 hour
        );

        return $server;
    }
}
