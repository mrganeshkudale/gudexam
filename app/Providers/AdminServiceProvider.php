<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use App\Admin\Admin;
use App\Admin\Admin1;
use Illuminate\Contracts\Support\DeferrableProvider;

class AdminServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Admin::class, function ($app)
        {
            return new Admin(Auth::user());
        });

        $this->app->singleton(Admin1::class, function ($app)
        {
            return new Admin1(Auth::user());
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
        return [Admin::class,Admin1::class];
    }
}
