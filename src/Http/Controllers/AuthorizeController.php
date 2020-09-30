<?php

namespace TGP\AuthServer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TGP\AuthServer\AuthServer;
use TGP\AuthServer\Services\Store\SessionStore;

class AuthorizeController extends Controller
{
    /**
     * The AuthServer instance.
     *
     * @var  AuthServer $authServer
     */
    protected $authServer;

    /**
     * The Store instance.
     *
     * @var  Store $store
     */
    protected $store;

    /**
     * Build the class.
     *
     * @param  AuthServer $authServer
     * @return void
     */
    public function __construct(AuthServer $authServer, SessionStore $store) {
        $this->authServer = $authServer;
        $this->store      = $store;
    }

    /**
     * Redirect to the authorization server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request)
    {
        $authorizationUrl = $this->authServer->getAuthorizationUrl();

        // set current url as redirect back.
        $this->store->set('return_to', url()->previous() ?? $this->authServer->getRedirectTo());

        return redirect($authorizationUrl);
    }

    /**
     * Handle the authorization callback.
     *
     * @param  Request $request
     * @return Response
     */
    public function callback(Request $request) {

        if( ! $request->has('code') ) return abort(404);

        $state = $this->authServer->getState();

        $token = $this->authServer->exchangeCodeIntoToken($request->code);

        $this->authServer->setAccessToken($token);

        $this->authServer->login();

        $this->authServer->resetState();

        return view('auth-server::redirector');
    }
}
