<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use App\Student\Student;
use Illuminate\Contracts\Support\DeferrableProvider;

class StudentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Student::class, function ($app)
        {
            return new Student(Auth::user());
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
        return [Student::class];
    }
}
