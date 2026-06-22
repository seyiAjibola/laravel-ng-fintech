<?php

namespace SeyiAjibola\NgFintech\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SeyiAjibola\NgFintech\Services\FintechServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Load our ServiceProvider into the test app.
     * This is how Testbench knows about our package.
     */
    protected function getPackageProviders($app): array
    {
        return [
            FintechServiceProvider::class,
        ];
    }

    /**
     * Set up aliases — so NgFintech:: works in tests.
     */
    protected function getPackageAliases($app): array
    {
        return [
            'NgFintech' => \SeyiAjibola\NgFintech\Facades\NgFintech::class,
        ];
    }

    /**
     * Define environment — keys, database, logging config.
     */
    protected function defineEnvironment($app): void
    {
        // Use SQLite in-memory for tests — fast, no setup needed
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Paystack test credentials
        $app['config']->set('fintech.payment.default', 'paystack');
        $app['config']->set('fintech.payment.drivers.paystack', [
            'enabled'    => true,
            'secret_key' => env('PAYSTACK_SECRET_KEY', 'sk_test_fake'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY', 'pk_test_fake'),
            'base_url'   => 'https://api.paystack.co',
        ]);

        // Disable logging by default in tests
        $app['config']->set('fintech.logging.enabled', false);
    }

    /**
     * Run package migrations into the in-memory SQLite database.
     */
    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );
    }
}