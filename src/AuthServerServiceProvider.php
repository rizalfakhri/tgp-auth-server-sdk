<?php

namespace TGP\AuthServer\Providers;

use TGP\AuthServer\AuthServer;
use Illuminate\Support\ServiceProvider;
use TGP\AuthServer\Services\Store\SessionStore;
use TGP\AuthServer\Contracts\Store as StoreContract;
use TGP\AuthServer\TGPUserProvider;
use Illuminate\Support\Facades\Auth;

class AuthServerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            StoreContract::class,
            SessionStore::class
        );

        $this->app->bind('tgp-auth-server', AuthServer::class);
        $this->app->singleton(AuthServer::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the routes.
        $this->loadRoutesFrom(__DIR__ . '/routes/auth-server-routes.php');

        // Register the views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'auth-server');

        // Publish the required thins.
        $this->publishes([
            __DIR__ . '/../../config/auth-server.php' => config_path('auth-server.php')
        ], 'auth-server');

        Auth::provider('tgp-auth-server', function($app, array $config = []) {
            return $app->make(TGPUserProvider::class);
        });
    }
}
