<?php

namespace TGP\AuthServer\Traits;

use TGP\AuthServer\Exceptions\InvalidClientCredentialsException;

trait InteractsWithClients {

    /**
     * Get the client access token.
     *
     * @return string
     * @throws InvalidCredentialsException
     */
    public function getClientAccessToken() {
        try {

            $tokenRes = $this->client->post(
                sprintf("%s/oauth/token", $this->getAuthServerEndpoint()),
                [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('auth-server.client_id'),
                        'client_secret' => config('auth-server.client_secret'),
                        'scope' => '*',
                    ],
                ]
            );

            $token = json_decode($tokenRes->getBody()->getContents(), true);

            if(isset($token['access_token'])) return $token['access_token'];

            throw new InvalidClientCredentialsException;

        } catch(\Exception $e) {
            throw new InvalidClientCredentialsException;
        }

    }

}

