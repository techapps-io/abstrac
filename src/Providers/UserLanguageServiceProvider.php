<?php

namespace Cygnis\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class UserLanguageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('language',function(){
            if (request()->has('language') && request()->get('language')) {
                return request()->get('language');
            } else if (session()->has('language') && session()->get('language')) {
                return session()->get('language');
            }
        });
    }
}
