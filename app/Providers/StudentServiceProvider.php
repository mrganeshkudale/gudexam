<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use App\Student\Student;
class StudentServiceProvider extends ServiceProvider
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
      $this->app->singleton(Student::class, function ($app)
      {
          return new Student(Auth::user());
      });
    }
}
