<?php

namespace TGP\AuthServer\Middleware;

use Closure;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use phpseclib\Crypt\RSA;
use Illuminate\Http\Request;
use TGP\AuthServer\AuthServer;
use TGP\AuthServer\Exceptions\NoRSAKeyProvidedException;
use Illuminate\Auth\AuthenticationException;
use TGP\AuthServer\TGPUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use TGP\AuthServer\TGPUserProvider;

class AuthServerMiddleware {

    /**
     * The AuthServer instance.
     *
     * @var  AuthServer $authServer
     */
    protected $authServer;

    /**
     * The GuzzleHttp Client.
     *
     * @var  Client $client
     */
    protected $client;

    /**
     * The cache expired time in minutes.
     *
     * @var  $cacheExpiration
     */
    protected $cacheExpiration = 60 * 2; // 2 hours

    /**
     * Build the middleware
     *
     * @param  AuthServe $authServer
     * @return void
     */
    public function __construct(AuthServer $authServer) {
        $this->authServer = $authServer;

        $this->client  = new Client([
            'base_uri' => $authServer->getAuthServerEndpoint()
        ]);
    }

    /**
     * Handle an incoming request.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if( ! is_null($request->bearerToken()) ) {

            $pub   = $this->authServer->getPublicKey();
            $token = $request->bearerToken();

            if( is_null($pub) ) throw new NoRSAKeyProvidedException();

            try {

                $decodedToken = (array) JWT::decode($token, $pub, ['RS256']);

                if(isset($decodedToken['user_info'])) {
                    $userInfo = $this->getUserFromEncryptedUserInfo($decodedToken['user_info']);

                    if(is_array($userInfo) && !empty($userInfo)) {
                        $this->authServer->setAccessToken($token);

                        $userInfo = $this->authServer->getUserInfo();

                        $request->setUserResolver(function() use($userInfo) {
                            return new TGPUser($userInfo);
                        });

                        Auth::login(new TGPUser($userInfo));

                        return $next($request);
                    }

                }
                else
                {

                    // fetch user by id to the auth server using JWT's sub claim.
                    // but for now just reject the request

                    $subClaim     = $decodedToken['sub'];
                    $cacheKey     = md5(sprintf("sub_%s", $subClaim));
                    $cachedInfo   = Cache::get($cacheKey);

                    if( is_null($cachedInfo) ) {

                        $userInfoRes = $this->client->get("/api/clients/users/{$subClaim}?with=client_access,client_roles", [
                            'headers' => [
                                'Authorization' => sprintf("Bearer %s", $this->authServer->getClientAccessToken())
                            ]
                        ]);

                        $rawInfo = json_decode($userInfoRes->getBody()->getContents(), true);

                        // cache the user info to minimize latency.
                        Cache::put($cacheKey, $rawInfo, now()->addMinutes($this->cacheExpiration));
                    }
                    else
                    {
                        $rawInfo = $cachedInfo;
                    }


                    $request->setUserResolver(function() use($rawInfo) {
                        return new TGPUser($rawInfo);
                    });

                    Auth::login(new TGPUser($rawInfo));

                    return $next($request);
                }

                throw new AuthenticationException;

            } catch(\Exception $e) {
                throw new AuthenticationException;
            }

        }

        throw new AuthenticationException;
    }

    /**
     * Decrypt the user info.
     *
     * @param  string $encryptedInfo
     * @return array|null
     *
     * @throws NoRSAKeyProvidedException
     */
    protected function getUserFromEncryptedUserInfo($encryptedInfo) {

        $priv = $this->authServer->getPublicKey();

        if( is_null($priv) ) throw new NoRSAKeyProvidedException();

        try {

            $rsa = new RSA();
            $rsa->loadKey($priv);

            $decoded = base64_decode($encryptedInfo);

            return (array) json_decode($rsa->decrypt($decoded));

        } catch(\Exception $e) {
            throw new AuthenticationException;
        }
    }

}
