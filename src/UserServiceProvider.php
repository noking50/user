<?php

namespace Noking50\User;

use Illuminate\Support\ServiceProvider;
use Noking50\Sitemap\Facades\Sitemap;

class UserServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('user', function () {
            $root = Sitemap::node()->getRoot();
            
            if(!$root->isEmpty()){
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
