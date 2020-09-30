<?php

namespace TGP\AuthServer\Facades;

use Illuminate\Support\Facades\Facade;

class AuthServer extends Facade {

    /**
     * Return the container binding for the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'tgp-auth-server';
    }
}

