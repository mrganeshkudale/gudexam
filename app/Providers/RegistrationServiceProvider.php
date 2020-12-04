<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Registration\Registration;
class RegistrationServiceProvider extends ServiceProvider
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
      $this->app->singleton(Registration::class, function ($app)
      {
          return new Registration($this->app->request);
      });
    }
}
