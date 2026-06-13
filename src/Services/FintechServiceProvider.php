<?php

namespace SeyiAjibola\NgFintech\Services;

use Illuminate\Support\ServiceProvider;
use SeyiAjibola\NgFintech\Services\FintechManager;

class FintechServiceProvider extends ServiceProvider
{
    /**
     * Register package services into the Laravel container.
     * This runs BEFORE boot(). Only bind things here — no config access yet.
     */
    public function register(): void
    {
        // Merge our config with the app's config.
        // This means if the user hasn't published config, defaults still work.
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/fintech.php',
            'fintech'
        );

        // Bind the FintechManager as a singleton.
        // Singleton = one shared instance throughout the app lifecycle.
        $this->app->singleton('fintech.payment', function ($app) {
            return new FintechManager($app, 'payment');
        });

        $this->app->singleton('fintech.airtime', function ($app) {
            return new FintechManager($app, 'airtime');
        });

        $this->app->singleton('fintech.bills', function ($app) {
            return new FintechManager($app, 'bills');
        });

        $this->app->singleton('fintech.identity', function ($app) {
            return new FintechManager($app, 'identity');
        });

        $this->app->singleton('fintech.banking', function ($app) {
            return new FintechManager($app, 'banking');
        });
    }

    /**
     * Bootstrap package services.
     * This runs AFTER register(). Safe to access config here.
     */
    public function boot(): void
    {
        // Only expose publish commands when running in console (artisan).
        if ($this->app->runningInConsole()) {

            // Allow users to publish config to their own app:
            // php artisan vendor:publish --tag=fintech-config
            $this->publishes([
                __DIR__ . '/../../config/fintech.php' => config_path('fintech.php'),
            ], 'fintech-config');

        }
    }

    /**
     * Declare what this provider provides.
     * Helps Laravel with deferred loading — only load when needed.
     */
    public function provides(): array
    {
        return [
            'fintech.payment',
            'fintech.airtime',
            'fintech.bills',
            'fintech.identity',
            'fintech.banking',
        ];
    }
}