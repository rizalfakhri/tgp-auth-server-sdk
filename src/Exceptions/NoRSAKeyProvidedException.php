<?php 

namespace BoneyBone\AuthServer\Exceptions;

use Exception;

class NoRSAKeyProvidedException extends Exception {

    /**
     * Build the exception.
     *
     * @return Exception
     */
    public function __construct() {
        return parent::__construct(
            "Please provide Public & Private RSA Key using AuthServer::loadPublicKey() nor AuthServer::loadPrivateKey() on your ServiceProvider."
        );
    }

}

