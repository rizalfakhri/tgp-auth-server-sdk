<?php

namespace TGP\AuthServer\Exceptions;

use Exception;

class MissingPermissionException extends Exception {

    /**
     * Throw the exception.
     *
     * @return Exception
     */
    public function __construct($permission) {
        return parent::__construct(
            sprintf("You need [%s] permission to access this endpoint.", $permission)
        );
    }

}

