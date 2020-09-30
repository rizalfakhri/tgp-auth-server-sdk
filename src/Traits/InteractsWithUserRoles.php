<?php

namespace TGP\AuthServer\Traits;

trait InteractsWithUserRoles {

    /**
     * Get the user roles.
     *
     * @param  boolean  $all Get all roles not limited by current client id
     * @return array
     */
    public function getRoles($all = false) {
        $userInfo = $this->getUserInfo(true);

        if(empty($userInfo)) return [];

        if(is_array($userInfo) && isset($userInfo['client_roles'])) {
            $roles = $userInfo['client_roles'];

            if(empty($roles)) return $roles;

            $clientId = config('auth-server.client_id');

            $roles = collect($roles)->map(function($role) {
                return [
                    'client_id'    => $role['client_id'],
                    'role_id'      => $role['id'],
                    'role_name'    => $role['name'],
                    'permissions'  => collect($role['permissions'])->map(function($permission) {
                        return [
                            'permission_id'    => $permission['id'],
                            'permission_name'  => $permission['name']
                        ];
                    })->toArray()
                ];
            });

            if($all) return $roles->toArray();

            return is_null($clientId)
                    ? $roles->toArray()
                    : $roles->filter(function($role) use($clientId) {
                        return $role['client_id'] === $clientId;
                    })->toArray();
        }

        return [];
    }

    /**
     * Get the user permissions.
     *
     * @param  bolean $all Get all assigned permissions, not limited byt current client id.
     * @return array
     */
    public function getPermissions($all = false) {
        $roles = $this->getRoles($all);

        if(empty($roles)) return [];

        $permissions = collect($roles)->map(function($role) {
            return collect($role['permissions'])->pluck('permission_name')->toArray();
        });

        return $permissions->flatten()->toArray();
    }

}
