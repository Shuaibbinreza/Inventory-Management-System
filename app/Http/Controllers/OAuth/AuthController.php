<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OAuthServerService;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response;


class AuthController extends Controller
{
    public function authorize(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $server = OAuthServerService::make();

        $psrRequest = ServerRequestFactory::fromGlobals();
        $psrResponse = new Response();

        try {
            $authRequest = $server->validateAuthorizationRequest($psrRequest);

            $authRequest->setUser(new class implements \League\OAuth2\Server\Entities\UserEntityInterface {
                public function getIdentifier() {
                    return auth()->id();
                }
            });

            $authRequest->setAuthorizationApproved(true);

            return $server->completeAuthorizationRequest($authRequest, $psrResponse);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function token(Request $request)
    {
        $server = OAuthServerService::make();

        $psrRequest = ServerRequestFactory::fromGlobals();
        $psrResponse = new Response();

        try {
            return $server->respondToAccessTokenRequest($psrRequest, $psrResponse);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
