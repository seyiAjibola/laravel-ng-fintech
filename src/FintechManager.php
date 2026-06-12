<?php

namespace SeyiAjibola\NgFintech\Services;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class FintechManager extends Manager
{
    /**
     * The service category this manager handles.
     * e.g. 'payment', 'airtime', 'bills', 'identity', 'banking'
     */
    protected string $category;

    public function __construct($app, string $category)
    {
        parent::__construct($app);
        $this->category = $category;
    }

    /**
     * Get the default driver name from config.
     * Laravel's Manager calls this automatically when no driver is specified.
     *
     * e.g. config('fintech.payment.default') → 'paystack'
     */
    public function getDefaultDriver(): string
    {
        $driver = config("fintech.{$this->category}.default");

        if (empty($driver)) {
            throw new InvalidArgumentException(
                "No default driver set for fintech category [{$this->category}]. " .
                "Check your fintech config or FINTECH_" . strtoupper($this->category) . "_DRIVER in .env"
            );
        }

        return $driver;
    }

    /**
     * Create a driver instance.
     * Overrides Manager's createDriver to add our enabled check and 
     * driver class resolution before Laravel's default behaviour.
     */
    protected function createDriver($driver): mixed
    {
        // Step 1: Check if this driver is enabled in config
        $this->assertDriverEnabled($driver);

        // Step 2: Resolve the driver class for this category
        $driverClass = $this->resolveDriverClass($driver);

        // Step 3: Get the driver's config
        $config = config("fintech.{$this->category}.drivers.{$driver}", []);

        // Step 4: Instantiate and return
        return new $driverClass($config);
    }

    /**
     * Guard: throw early if driver is disabled.
     * Prevents silent failures — you'll know immediately if you call
     * a driver that's turned off in config.
     */
    protected function assertDriverEnabled(string $driver): void
    {
        $enabled = config("fintech.{$this->category}.drivers.{$driver}.enabled", false);

        if (! $enabled) {
            throw new InvalidArgumentException(
                "The [{$driver}] driver for [{$this->category}] is not enabled. " .
                "Set " . strtoupper($driver) . "_ENABLED=true in your .env file."
            );
        }
    }

    /**
     * Map driver name → fully qualified class name.
     * Add new drivers here as we build them.
     */
    protected function resolveDriverClass(string $driver): string
    {
        $map = [
            // Payment drivers
            'paystack'     => \SeyiAjibola\NgFintech\Drivers\Payment\PaystackDriver::class,
            'flutterwave'  => \SeyiAjibola\NgFintech\Drivers\Payment\FlutterwaveDriver::class,
            'monnify'      => \SeyiAjibola\NgFintech\Drivers\Payment\MonnifyDriver::class,

            // Airtime & Data drivers
            'vtpass'       => \SeyiAjibola\NgFintech\Drivers\Airtime\VTPassDriver::class,
            'clubkonnect'  => \SeyiAjibola\NgFintech\Drivers\Airtime\ClubkonnectDriver::class,
            'nellobytes'   => \SeyiAjibola\NgFintech\Drivers\Airtime\NellobytesDriver::class,

            // Bills drivers
            'buypower'     => \SeyiAjibola\NgFintech\Drivers\Bills\BuypowerDriver::class,
            'baxi'         => \SeyiAjibola\NgFintech\Drivers\Bills\BaxiDriver::class,

            // Identity drivers
            'mono'         => \SeyiAjibola\NgFintech\Drivers\Identity\MonoDriver::class,
            'okra'         => \SeyiAjibola\NgFintech\Drivers\Identity\OkraDriver::class,
            'prembly'      => \SeyiAjibola\NgFintech\Drivers\Identity\PremblyDriver::class,

            // Banking drivers
            'sterling'     => \SeyiAjibola\NgFintech\Drivers\Banking\SterlingDriver::class,
        ];

        if (! array_key_exists($driver, $map)) {
            throw new InvalidArgumentException(
                "Driver [{$driver}] is not supported. " .
                "Supported drivers: " . implode(', ', array_keys($map))
            );
        }

        return $map[$driver];
    }

    /**
     * Attempt the default driver — if it fails, try the fallback.
     * This is the Nigerian fintech safety net.
     * 
     * Usage: NgPayment::withFallback()->initializeTransaction([...])
     */
    public function withFallback(): static
    {
        $fallback = config("fintech.{$this->category}.fallback");

        try {
            $this->driver($this->getDefaultDriver());
        } catch (\Throwable $e) {
            if ($fallback) {
                $this->setDefaultDriver($fallback);
            }
        }

        return $this;
    }
}