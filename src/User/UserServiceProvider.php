<?php

namespace Noking50\User;

use Illuminate\Support\ServiceProvider;
use Sitemap;

class UserServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot() {
        $this->publishes([
            __DIR__ . '/../config/user.php' => config_path('user.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('user', function () {
            $root = Sitemap::node()->getRoot();

            if (!$root->isEmpty()) {
                return new User($root->prop('login_group'));
            } else {
                return new User;
            }
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['user'];
    }

}
