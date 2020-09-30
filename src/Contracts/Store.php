<?php

namespace TGP\AuthServer\Contracts;

interface Store {

    /**
     * Set item to the store.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Get the item from store using its key.
     *
     * @param  string $key
     * @param  mixed  $default  The dfault value to return if no data found in the store.
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Delete item from store using its key.
     *
     * @param  string $key
     * @return void
     */
    public function delete($key);
}

