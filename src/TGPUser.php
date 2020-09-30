<?php

namespace TGP\AuthServer;

use Illuminate\Contracts\Auth\Authenticatable;
use TGP\AuthServer\Traits\InteractsWithUserRoles;
use TGP\AuthServer\Traits\UserAccess;

class TGPUser implements Authenticatable {

    use InteractsWithUserRoles, UserAccess;

    /**
     * The AuthServer instnace.
     *
     * @var  AuthServer $authServer
     */
    protected $authServer;

    /**
     * The Raw User Info.
     *
     * @var  array $userIndo
     */
    protected $userInfo = [];

    /**
     * Build the Authenticatable.
     *
     * @param AuthServer  $authServer
     * @param $accessToken
     */
    public function __construct(array $userInfo)
    {
        $this->userInfo = $userInfo;
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        if(isset($this->userInfo['id'])) return $this->userInfo['id'];
    }

    /**
     * Get id field name.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        //
    }

    /**
     * @return void
     */
    public function getRememberToken()
    {
        //
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
        //
    }

    /**
     * @return void
     */
    public function getRememberTokenName()
    {
        //
    }

    /**
     * Add a generic getter to get all the properties of the userInfo.
     *
     * @return the related value or null if it is not set
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->userInfo)) {
            return;
        }

        return $this->userInfo[$name];
    }

    /**
     * @return mixed
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->userInfo, JSON_PRETTY_PRINT);
    }

}
