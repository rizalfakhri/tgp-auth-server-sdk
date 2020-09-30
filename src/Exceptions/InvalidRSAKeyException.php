<?php

namespace TGP\AuthServer\Exceptions;

use Exception;

class InvalidRSAKeyException extends Exception {

    /**
     * Build the Exception instance.
     *
     * @return Exception
     */
    public function __construct($path) {
        return parent::__construct(
            sprintf("The key path [%s] were contains invalid key.", $path)
        );
    }

}

