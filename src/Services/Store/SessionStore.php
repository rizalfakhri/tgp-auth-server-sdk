<?php

namespace TGP\AuthServer\Services\Store;

use Session;
use TGP\AuthServer\Contracts\Store as StoreContract;

class SessionStore implements StoreContract {

    /**
     * The key prefix.
     *
     * @var  string $prefix
     */
    protected $prefix = 'bb_auth_';

    /**
     * Set item to the store.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value) {
        Session::put($this->formatKey($key), $value);
    }

    /**
     * Get the item from store using its key.
     *
     * @param  string $key
     * @param  mixed  $default  The dfault value to return if no data found in the store.
     * @return mixed
     */
    public function get($key, $default = null) {
        $key = $this->formatKey($key);

        return Session::get($key, $default);
    }

    /**
     * Delete item from store using its key.
     *
     * @param  string $key
     * @return void
     */
    public function delete($key) {
        $key = $this->formatKey($key);

        Session::forget($key);
    }

    /**
     * Combine the original key with the prefix.
     *
     * @param  string $key
     * @return string
     */
    public function formatKey($key) {
        return sprintf("%s%s", $this->prefix, $key);
    }
}

