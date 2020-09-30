<?php

namespace TGP\AuthServer\Traits;

trait UserAccess {

    /**
     * Determine if the user "can" do a permission.
     *
     * @param  string $permission
     * @return bool
     */
    public function can($permission) {
        return (bool) in_array($permission, $this->getPermissions());
    }

    /**
     * Determine if the user "can't" do a permission.
     *
     * @param  string $permission
     * @return bool
     */
    public function cant($permission) {
        return ! $this->can($permission);
    }

}
