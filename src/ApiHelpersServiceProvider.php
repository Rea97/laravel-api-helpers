<?php

namespace ReaDev\ApiHelpers;

use Illuminate\Support\ServiceProvider;

class ApiHelpersServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ .'/../config/api-helpers.php' => config_path('api-helpers.php')
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
