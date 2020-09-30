<?php

namespace TGP\AuthServer\Exceptions;

use Exception;

class InvalidAuthorizationCodeException extends Exception {

    /**
     * Throw the exception.
     *
     * @return Exception
     */
    public function __construct() {
        return parent::__construct("The given Authorization Code is invalid.");
    }

}

