<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Fix for SwiftMailer Service;
        $_SERVER["SERVER_NAME"] = "www.salutlabs.com";

        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
    }
}
