<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\CustomLogin\CustomLogin;

class LoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(CustomLogin::class, function ($app)
        {
            return new CustomLogin($this->app->request);
        });
    }
}