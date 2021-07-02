<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\CustomLogin\CustomLogin;
use Illuminate\Contracts\Support\DeferrableProvider;

class LoginServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CustomLogin::class, function ($app)
        {
            return new CustomLogin($this->app->request);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     *
    public function boot()
    {
       
    }*/

    public function provides()
    {
        return [CustomLogin::class];
    }
}