<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Registration\Registration;
use Illuminate\Contracts\Support\DeferrableProvider;

class RegistrationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Registration::class, function ($app)
        {
            return new Registration($this->app->request);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     
    public function boot()
    {
      
    }*/

    public function provides()
    {
        return [Registration::class];
    }
}
