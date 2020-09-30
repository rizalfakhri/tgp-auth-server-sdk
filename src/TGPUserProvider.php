<?php

namespace TGP\AuthServer;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class TGPUserProvider implements UserProvider {

    /**
     * The AuthServer instance.
     *
     * @var  AuthServer $authServer
     */
    protected $authServer;

    /**
     * Build the class.
     *
     * @param  AuthServer $authServer
     * @return void
     */
    public function __construct(AuthServer $authServer) {
        $this->authServer = $authServer;
    }

	/**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier) {
        $user = $this->authServer->getUser();

        if($user instanceof Authenticatable) return $user;

        if(is_null($user)) {
            $userInfo = $this->authServer->getUserInfo();

            return new TGPUser($userInfo);
        }


        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token) {
        //
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token) {
        //
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials) {
        if(isset($credentials['access_token'])) {
            return new static($this->authServer->setAccessToken($credentials['access_token']));
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials) {
        return true;
    }

}
