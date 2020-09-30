<?php

namespace TGP\AuthServer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TGP\AuthServer\AuthServer;
use Illuminate\Support\Facades\Auth;

class DeauthorizeController extends Controller
{
    /**
     * Build the Controller class.
     *
     * @return void
     */
    public function __construct(AuthServer $authServer) {
        $this->middleware('auth');

        $this->authServer = $authServer;
    }

    /**
     * Handle the deauthorize request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deauthorize(Request $request)
    {
        if( ! $this->authServer->isLoggedIn() ) return abort(404);

        $this->authServer->logout();

        Auth::logout();

        return redirect(sprintf("%s/%s?returnTo=%s",
            $this->authServer->getAuthServerEndpoint(),
            'logout',
            url('/')
        ));

    }
}
