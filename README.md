<div align="center">

<h1>🇳🇬 laravel-ng-fintech</h1>

<p>
  <strong>A unified, driver-based Laravel package for Nigerian fintech APIs</strong><br/>
  One interface. Every provider. Swap without rewriting your app.
</p>

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10|11|12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Status](https://img.shields.io/badge/Status-In%20Development-orange)]()
[![Author](https://img.shields.io/badge/Author-Seyi%20Ojo-1B3A6B)](https://github.com/seyiAjibola)

</div>

---

## The Problem

Every Nigerian Laravel developer integrates Paystack, then later adds
Flutterwave as a fallback. Then VTPass for utilities. Then Dojah for KYC.
Then Mono for open banking.

Each integration is a different API style, different response shape,
different webhook signature, different error format. Your codebase ends up
tightly coupled to whichever provider you started with — and swapping
providers means rewriting business logic.

**laravel-ng-fintech solves this.**

---

## The Solution

A single, consistent interface modelled after Laravel's own `Mail`, `Cache`,
and `Queue` systems — driver-based, config-driven, and swappable at runtime.

````php
// Pay with Paystack today
Fintech::payment()->initializeTransaction($data);

// Switch to Flutterwave tomorrow — zero app code changes
// Just update FINTECH_PAYMENT_DRIVER=flutterwave in .env
Fintech::payment()->initializeTransaction($data);

// Verify BVN
Fintech::identity()->verifyBvn($bvn);

// Pay DSTV subscription
Fintech::bills()->payBill(['biller_code' => 'dstv', ...]);

// Buy MTN airtime
Fintech::airtime()->purchaseAirtime(['phone' => '08012345678', 'amount' => 1000]);

// Get account statement via open banking
Fintech::banking()->getAccountStatement($monoId);
````

---

## Services & Drivers

| Service | Supported Drivers | Status |
|---|---|---|
| **Payment** | Paystack, Flutterwave, Monnify | 🚧 In Development |
| **Bills** | VTPass, BuyPower | 🚧 In Development |
| **Airtime & Data** | VTPass, Clubconnect | 🚧 In Development |
| **Identity / KYC** | Dojah, Prembly | 🚧 In Development |
| **Open Banking** | Mono | 🚧 In Development |
| **Betting Wallets** | VTPass | 🚧 In Development |

---

## Installation

````bash
composer require seyiajibola/laravel-ng-fintech
````

Publish the config:

````bash
php artisan vendor:publish --tag="fintech-config"
````

---

## Configuration

Every service and every driver can be toggled independently from your
`.env` file — no code changes required.

````env
# ── Master service switches ─────────────────────────────
FINTECH_PAYMENT_ENABLED=true
FINTECH_BANKING_ENABLED=false
FINTECH_IDENTITY_ENABLED=false
FINTECH_AIRTIME_ENABLED=false
FINTECH_BILLS_ENABLED=false

# ── Active drivers ──────────────────────────────────────
FINTECH_PAYMENT_DRIVER=paystack
FINTECH_BANKING_DRIVER=mono
FINTECH_IDENTITY_DRIVER=dojah
FINTECH_AIRTIME_DRIVER=vtpass
FINTECH_BILLS_DRIVER=vtpass

# ── Paystack ────────────────────────────────────────────
PAYSTACK_ENABLED=true
PAYSTACK_PUBLIC_KEY=pk_live_xxx
PAYSTACK_SECRET_KEY=sk_live_xxx
PAYSTACK_WEBHOOK_SECRET=xxx

# ── Flutterwave (fallback) ──────────────────────────────
FLUTTERWAVE_ENABLED=true
FLW_PUBLIC_KEY=FLWPUBK_xxx
FLW_SECRET_KEY=FLWSECK_xxx

# ── VTPass (bills + airtime) ────────────────────────────
VTPASS_ENABLED=true
VTPASS_API_KEY=xxx
VTPASS_PUBLIC_KEY=xxx
VTPASS_SECRET_KEY=xxx
VTPASS_ENV=sandbox
````

---

## Usage

### Payment

````php
use Fintech;

// Initialise a transaction
$response = Fintech::payment()->initializeTransaction([
    'email'    => 'customer@email.com',
    'amount'   => 5000,
    'currency' => 'NGN',
    'callback' => route('payment.callback'),
]);

// Returns a normalised DTO — same shape regardless of driver
$response->authorizationUrl; // redirect user here
$response->reference;        // save this for verification

// Verify a transaction
$verified = Fintech::payment()->verifyTransaction($reference);

$verified->status;  // 'success' | 'failed' | 'pending'
$verified->amount;  // always in Naira (we handle kobo conversion)
$verified->channel; // 'card' | 'bank_transfer' | 'ussd'

// Resolve a bank account
$account = Fintech::payment()->resolveAccount('0123456789', '044');
$account->accountName;   // "JOHN DOE"
$account->bankCode;      // "044"
````

### Airtime & Data

````php
// Buy airtime
$result = Fintech::airtime()->purchaseAirtime([
    'phone'   => '08012345678',
    'network' => 'MTN',
    'amount'  => 1000,
]);

// Get available data plans
$plans = Fintech::airtime()->getDataPlans('AIRTEL');

// Buy a data bundle
$result = Fintech::airtime()->purchaseData([
    'phone'     => '08012345678',
    'network'   => 'GLO',
    'plan_code' => 'glo-1gb-30days',
]);
````

### Bill Payments

````php
// Validate customer before payment (always do this first)
$customer = Fintech::bills()->validateCustomer('dstv', '7042552422');
$customer->name;    // "JOHN DOE"
$customer->status;  // 'active'

// Get available bouquets
$plans = Fintech::bills()->getBillerPlans('dstv');

// Pay DSTV
$result = Fintech::bills()->payBill([
    'biller_code' => 'dstv',
    'customer_id' => '7042552422',
    'plan_code'   => 'dstv-compact',
    'amount'      => 15000,
]);

// Pay electricity (prepaid)
Fintech::bills()->validateCustomer('ekedc-prepaid', '12345678901');
$result = Fintech::bills()->payBill([
    'biller_code'  => 'ekedc-prepaid',
    'meter_number' => '12345678901',
    'amount'       => 5000,
]);

$result->token;      // prepaid meter token
$result->reference;  // transaction reference
````

### Identity / KYC

````php
// Verify BVN
$bvn = Fintech::identity()->verifyBvn('12345678901');
$bvn->firstName;
$bvn->lastName;
$bvn->phone;
$bvn->dateOfBirth;

// Verify NIN
$nin = Fintech::identity()->verifyNin('12345678901');

// Facial verification
$match = Fintech::identity()->compareFace($selfieBase64, $bvn);
$match->confidence; // 0-100
$match->matched;    // true | false
````

### Open Banking

````php
// Get account statement
$statement = Fintech::banking()->getAccountStatement($monoAccountId);

// Get account balance
$balance = Fintech::banking()->getAccountBalance($monoAccountId);
$balance->available; // in Naira
$balance->currency;  // 'NGN'
````

### Switching Drivers at Runtime

````php
// Use default driver (from .env)
Fintech::payment()->initializeTransaction($data);

// Explicitly use a specific driver for this request
Fintech::payment('flutterwave')->initializeTransaction($data);
Fintech::payment('monnify')->initializeTransaction($data);

// Useful for: A/B testing, per-merchant routing, fallback logic
````

### Webhook Handling

Each driver verifies its own webhook signature — your controller stays clean:

````php
// routes/api.php
Route::post('/webhooks/paystack',    [WebhookController::class, 'paystack']);
Route::post('/webhooks/flutterwave', [WebhookController::class, 'flutterwave']);

// app/Http/Controllers/WebhookController.php
public function paystack(Request $request)
{
    $payload = Fintech::payment('paystack')->verifyWebhook($request);

    if ($payload->event === 'charge.success') {
        // handle successful payment
    }
}
````

---

## Architecture

This package is built on Laravel's **Manager / Driver pattern** — the same
pattern used internally by `Cache`, `Queue`, `Mail`, and `Filesystem`.

````
FintechManager
├── PaymentService
│   ├── PaystackDriver     implements PaymentProvider
│   ├── FlutterwaveDriver  implements PaymentProvider
│   └── MonnifyDriver      implements PaymentProvider
├── AirtimeService
│   └── VTPassDriver       implements AirtimeProvider
├── BillsService
│   ├── VTPassDriver       implements BillsProvider
│   └── BuyPowerDriver     implements BillsProvider
├── IdentityService
│   ├── DojahDriver        implements IdentityProvider
│   └── PremblyDriver      implements IdentityProvider
└── BankingService
    └── MonoDriver         implements BankingProvider
````

**Every driver returns normalised DTOs** — your application code never
touches raw API responses. Switching providers never breaks your business logic.

---

## Design Principles

- **Contract-first** — every service category has a PHP interface that all drivers must implement
- **Normalised responses** — consistent DTO shapes regardless of which driver is active
- **Config-driven** — enable, disable, or swap any driver from `.env` without touching code
- **Fallback support** — define fallback drivers for automatic failover
- **Driver-level retries** — HTTP retries happen inside the driver, not in your app
- **Webhook verification** — each driver handles its own signature verification
- **Idempotency** — built-in idempotency key support to prevent duplicate transactions
- **Full logging** — every request and response is logged for audit and dispute resolution

---

## Roadmap

- [ ] Paystack driver (payment, transfer, bank resolution)
- [ ] Flutterwave driver
- [ ] Monnify driver
- [ ] VTPass driver (airtime, data, bills, betting)
- [ ] BuyPower driver (electricity specialist)
- [ ] Dojah driver (BVN, NIN, facial)
- [ ] Prembly driver
- [ ] Mono driver (open banking)
- [ ] Fallback strategy engine
- [ ] Webhook event system
- [ ] Laravel Nova integration
- [ ] Filament integration
- [ ] Full Pest test suite
- [ ] Packagist release

---

## Contributing

Contributions are welcome — especially new drivers. If you want to add
support for a Nigerian fintech provider not listed here, please open an
issue first so we can discuss the contract design.

````bash
git clone https://github.com/seyiAjibola/laravel-ng-fintech
cd laravel-ng-fintech
composer install
cp .env.example .env
php artisan test
````

---

## Author

**Seyi Ojo** — Senior Laravel Engineer, Lagos Nigeria 🇳🇬

[![LinkedIn](https://img.shields.io/badge/LinkedIn-seyiojo-0077B5?logo=linkedin)](https://linkedin.com/in/seyiojo)
[![GitHub](https://img.shields.io/badge/GitHub-seyiAjibola-181717?logo=github)](https://github.com/seyiAjibola)

---

## License

MIT — free to use in personal and commercial projects.

---

<div align="center">
  <sub>Built with ❤️ for the Nigerian developer community</sub>
</div>