<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use App\Admin\Admin;
use App\Admin\Admin1;
class AdminServiceProvider extends ServiceProvider
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
      $this->app->singleton(Admin::class, function ($app)
      {
          return new Admin(Auth::user());
      });

      $this->app->singleton(Admin1::class, function ($app)
      {
          return new Admin1(Auth::user());
      });
    }
}
