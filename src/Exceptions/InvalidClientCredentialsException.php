<?php

namespace TGP\AuthServer\Exceptions;

use Exception;

class InvalidClientCredentialsException extends Exception  {

    /**
     * Build the Exception class.
     *
     * @return Exception
     */
    public function __construct() {
        return parent::__construct(
            "The Client Credentials were Invalid."
        );
    }

}


