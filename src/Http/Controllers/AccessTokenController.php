<?php

namespace TGP\AuthServer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TGP\AuthServer\AuthServer;

class AccessTokenController extends Controller
{

    /**
     * Build the controller.
     *
     * @param  AuthServer $authServer
     */
    public function __construct(AuthServer $authServer)  {
        $this->authServer = $authServer;
    }

    /**
     * Exchange authorization code into JWT Token.
     *
     * @param  Request  $request
     * @return Response
     */
    public function exchangeAuthorizationCode(Request $request) {
        if( ! $request->has('code') ) {
            return response()->json([
                "error"   => true,
                "message" => "Missing Authorization Code."
            ], 400);
        }

        try {

            $accessToken = $this->authServer->exchangeAuthorizationCode($request->code);

            if(is_array($accessToken)) {
                return response()->json($accessToken, 200);
            }

            throw new \Exception("Invalid Authorization Code");

        } catch(\Exception $e) {

            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage()
            ], 400);

        }

    }

    /**
     * Refresh the access token using the refresh token.
     *
     * @param  Request $request
     * @return Response
     */
    public function refreshAccessToken(Request $request) {
         if( ! $request->has('refresh_token') ) {
            return response()->json([
                "error"   => true,
                "message" => "Missing Refresh Token."
            ], 400);
        }

        try {

            $accessToken = $this->authServer->refreshAccessToken($request->refresh_token);

            if(is_array($accessToken)) {
                return response()->json($accessToken, 200);
            }

            throw new \Exception("Invalid Refresh Token.");

        } catch(\Exception $e) {

            return response()->json([
                'error'    => true,
                'message'  => $e->getMessage()
            ], 400);

        }


    }
}
