<?php

namespace TGP\AuthServer;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use phpseclib\Crypt\RSA;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Auth\AuthenticationException;
use TGP\AuthServer\Contracts\Store as StoreContract;
use TGP\AuthServer\Exceptions\InvalidAuthorizationCodeException;
use TGP\AuthServer\Exceptions\InvalidRSAKeyException;
use TGP\AuthServer\Exceptions\RSAKeyNotFoundException;
use TGP\AuthServer\Traits\InteractsWithClients;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Firebase\JWT\BeforeValidException;
use TGP\AuthServer\TGPUser;

class AuthServer {

    use InteractsWithClients;

    /**
     * The Store instance.
     *
     * @var  StoreContract $store
     */
    public $store;

    /**
     * The Client instance.
     *
     * @var  Client $client
     */
    protected $client;

    /**
     * The redirect path after authenticated.
     *
     * @var  string $redirectTo
     */
    protected $redirectTo;

    /**
     * The RSA Public Key.
     *
     * @var  string $publicKey
     */
    protected $publicKey;

    /**
     * The RSA Private Key.
     *
     * @var  string $privateKey
     */
    protected $privateKey;

    /**
     * Build the class.
     *
     * @param  StoreContract $store
     * @return void
     */
    public function __construct(StoreContract $store) {
        $this->store   = $store;
        $this->client  = new Client([
            'base_uri' => $this->getAuthServerEndpoint()
        ]);
    }

    /**
     * Exchange Authorization Code into Access Token Native Response.
     *
     * @param  string $code
     * @return array
     */
    public function exchangeAuthorizationCode($code) {
        try {

            $tokenResponse = $this->client->post(
                sprintf("%s/oauth/token", $this->getAPIGatewayServerEndpoint()), [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'client_id' => config('auth-server.client_id'),
                        'client_secret' => config('auth-server.client_secret'),
                        'redirect_uri' => config('auth-server.redirect_uri'),
                        'code' => $code,
                    ],
                ]
            );

            $res = json_decode($tokenResponse->getBody()->getContents(), true);

            //$res['access_token'] = $this->registerUserRolesIntoAccessToken($res['access_token']);

            return $res;

            throw new InvalidAuthorizationCodeException;

        } catch(\Exception $e) {

            throw new InvalidAuthorizationCodeException;

        }
    }

    /**
     * Refresh access token.
     *
     * @param  string $refreshToken
     * @return array
     */
    public function refreshAccessToken($refreshToken) {
        try {

            $tokenResponse = $this->client->post(
                sprintf("%s/oauth/token", $this->getAuthServerEndpoint()), [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => config('auth-server.client_id'),
                        'client_secret' => config('auth-server.client_secret'),
                        'refresh_token' => $refreshToken,
                    ],
                ]
            );

            $res = json_decode($tokenResponse->getBody()->getContents(), true);

            return $res;

            throw new InvalidAuthorizationCodeException;

        } catch(\Exception $e) {

            throw new InvalidAuthorizationCodeException;

        }

    }

    /**
     * Exchange authorization code into access token.
     *
     * @param  string $code
     * @return string
     */
    public function exchangeCodeIntoToken($code) {
        try {

            return $this->exchangeAuthorizationCode($code)['access_token'];

            throw new InvalidAuthorizationCodeException;

        } catch(\Exception $e) {

            throw new InvalidAuthorizationCodeException;

        }
    }

    /**
     * Get the authorization url.
     *
     * @return  string
     */
    public function getAuthorizationUrl() {

        $state = $this->getState();

        $query = http_build_query([
			'client_id' => config('auth-server.client_id'),
			'redirect_uri' => config('auth-server.redirect_uri'),
			'response_type' => 'code',
			'scope' => '',
			'state' => $state,
		]);

        return sprintf('%s/oauth/authorize?%s', $this->getAuthServerEndpoint(), $query);

    }

    /**
     * Get user info.
     *
     * @param  bool $fresh Get fresh data instead of loading from cache.
     * @return array|null
     */
    public function getUserInfo($fresh = false) {
        if( is_array($userInfo = $this->store->get('user_info')) && false === $fresh) {
            return $userInfo;
        }

        try {

            $userInfoRes = $this->client->get('/api/me?with=client_access,client_roles', [
                'headers' => [
                    'Authorization' => sprintf("Bearer %s", $this->getAccessToken())
                ]
            ]);

            $this->store->set(
                'user_info',
                $userInfo = json_decode($userInfoRes->getBody()->getContents(), true)
            );

            return $userInfo;

        } catch(\Exception $e) {

            if( $e->getCode() == 401 ) throw new AuthenticationException;

            throw $e;

        }
    }

    /**
     * Logging in the user.
     *
     * @return void
     */
    public function login() {
        $info = $this->getUserInfo();

        $user = new TGPUser($info);

        Auth::login($user);

        $this->store->set('isLoggedIn', true);

    }

    /**
     * Detemine if the user currently logged in.
     *
     * @return bool
     */
    public function isLoggedIn() {
        return (bool) $this->store->get('isLoggedIn');
    }

    /**
     * Get the authenticated user.
     *
     * @return mixed
     */
    public function getUser() {
        return $this->store->get('user');
    }

    /**
     * Set the redirect path after authenticated.
     *
     * @param  string $path
     * @return void
     */
    public function redirectTo($path) {
        return $this->redirectTo = $path;
    }

    /**
     * Get redirect path after authenticated.
     *
     * @return string|null
     */
    public function getRedirectTo() {

        if( $this->store->get('return_to') ) {
            return $this->store->get('return_to');
        }

        return $this->redirectTo;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState() {
        if( ! is_null($this->store->get('state')) ) return $this->store->get('state');

        $this->store->set('state', $state = Str::random(50));

        return $state;
    }

    /**
     * Reset the state.
     *
     * @return void
     */
    public function resetState() {
        if( ! is_null($this->store->get('state')) ) $this->store->delete('state');
    }

    /**
     * Set the access token.
     *
     * @param  string $accessToken
     * @return void
     */
    public function setAccessToken($accessToken) {
        return $this->store->set('access_token', $accessToken);
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken() {
        return $this->store->get('access_token');
    }

    /**
     * Get the Authorization Server Endpoint.
     *
     * @return string
     */
    public function getAuthServerEndpoint() {
        return config('auth-server.endpoint');
    }

    /**
     * Get the API Gateway Server Endpoint.
     *
     * @return string
     */
    public function getAPIGatewayServerEndpoint() {
        return config('auth-server.endpoint');
    }

    /**
     * Logout the current user.
     *
     * @return void
     */
    public function logout() {
        return app(Pipeline::class)
                    ->send($this)
                    ->through([
                        function($passable) {
                            $passable->store->delete('user');
                            $passable->store->delete('access_token');
                            $passable->store->delete('isLoggedIn');
                            $passable->store->delete('state');
                            $passable->store->delete('user_info');
                        }
                    ])
                    ->thenReturn();

    }

    /**
     * Load the public key from the provided path.
     *
     * @param  string $path
     * @return void
     *
     * @throws InvalidRSAKeyException   when the key is invalid format
     * @throws RSAKeyNotFoundException  when the key is not found on the provided path
     */
    public function loadPublicKey($path) {
        if(file_exists($path)) {
            $key = file_get_contents($path);

            $rsa = new RSA();

            if( false === $rsa->loadKey($key) )
                throw new InvalidRSAKeyException($path);

            return $this->publicKey = $key;
        }

        throw new RSAKeyNotFoundException($path);
    }

    /**
     * Load the private key from the provided path.
     *
     * @param  string $path
     * @return void
     *
     * @throws InvalidRSAKeyException   when the key is invalid format
     * @throws RSAKeyNotFoundException  when the key is not found on the provided path
     */
    public function loadPrivateKey($path) {
        if(file_exists($path)) {
            $key = file_get_contents($path);

            $rsa = new RSA();

            if( false === $rsa->loadKey($key) )
                throw new InvalidRSAKeyException($path);

            return $this->privateKey = $key;
        }

        throw new RSAKeyNotFoundException($path);
    }

    /**
     * Get the RSA Public Key.
     *
     * @return string|null
     */
    public function getPublicKey() {

        if( is_null($this->publicKey) && file_exists(storage_path('oauth-public.key'))) {
            return file_get_contents(
                storage_path('oauth-public.key')
            );
        }

        return $this->publicKey;
    }

    /**
     * Get the RSA Private Key.
     *
     * @return string|null
     */
    public function getPrivateKey() {

        if( is_null($this->privateKey) && file_exists(storage_path('oauth-private.key'))) {
            return file_get_contents(
                storage_path('oauth-private.key')
            );
        }

        return $this->privateKey;
    }
}
