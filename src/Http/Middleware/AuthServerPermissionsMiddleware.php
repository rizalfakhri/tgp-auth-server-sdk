<?php

namespace TGP\AuthServer\Middleware;

use Closure;
use TGP\AuthServer\AuthServer;
use TGP\AuthServer\TGPUser;
use Illuminate\Auth\AuthenticationException;
use TGP\AuthServer\Exceptions\MissingPermissionException;

class AuthServerPermissionsMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Closure                 $next
     * @param  mixed                   $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions) {

        if( ! $request->user() )
            throw new AuthenticationException;


        if( ! $request->user() instanceof TGPUser )
            throw new AuthenticationException;

        foreach($permissions as $permission) {
            if( ! in_array($permission, $request->user()->getPermissions()) ) {
                throw new MissingPermissionException($permission);
            }
        }

        return $next($request);
    }
}
