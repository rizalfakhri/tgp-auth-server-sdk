<?php 

namespace BoneyBone\AuthServer\Exceptions;

use Exception;

class RSAKeyNotFoundException extends Exception {

    /**
     * Build the exception.
     *
     * @param  string $path
     * @return Exception
     */
    public function __construct($path) {
        return parent::__construct(
            sprintf("The key path [%s] you provided was not found.", $path)
        );
    }


}

