<?php

use SeyiAjibola\NgFintech\Drivers\Payment\PaystackDriver;
use SeyiAjibola\NgFintech\Exceptions\FintechException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;

beforeEach(function () {
    // Base config for every test
    $this->config = [
        'enabled'    => true,
        'secret_key' => 'sk_test_fake_key',
        'public_key' => 'pk_test_fake_key',
        'base_url'   => 'https://api.paystack.co',
    ];
});

afterEach(function () {
    m::close();
});

// -------------------------------------------------------
// initializeTransaction
// -------------------------------------------------------

it('initializes a transaction successfully', function () {
    // Mock Guzzle response
    $mockResponse = new Response(200, [], json_encode([
        'status'  => true,
        'message' => 'Authorization URL created',
        'data'    => [
            'authorization_url' => 'https://checkout.paystack.com/test123',
            'access_code'       => 'test123',
            'reference'         => 'NGF-123456-ABCDEF',
        ],
    ]));

    $mockClient = m::mock(Client::class);
    $mockClient->shouldReceive('post')
               ->once()
               ->andReturn($mockResponse);

    $driver = new PaystackDriver($this->config);

    // Inject mock client via reflection
    $reflection = new ReflectionClass($driver);
    $property   = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($driver, $mockClient);

    $result = $driver->initializeTransaction([
        'email'  => 'test@example.com',
        'amount' => 10000,
    ]);

    expect($result)->toBeArray()
        ->and($result['status'])->toBeTrue()
        ->and($result['data'])->toHaveKey('authorization_url')
        ->and($result['data'])->toHaveKey('reference');
});

it('throws FintechException when email is missing', function () {
    $driver = new PaystackDriver($this->config);

    $driver->initializeTransaction([
        'amount' => 10000,
        // email missing
    ]);
})->throws(FintechException::class, 'email');

it('throws FintechException when amount is missing', function () {
    $driver = new PaystackDriver($this->config);

    $driver->initializeTransaction([
        'email' => 'test@example.com',
        // amount missing
    ]);
})->throws(FintechException::class, 'amount');

// -------------------------------------------------------
// verifyTransaction
// -------------------------------------------------------

it('verifies a transaction successfully', function () {
    $mockResponse = new Response(200, [], json_encode([
        'status'  => true,
        'message' => 'Verification successful',
        'data'    => [
            'status'    => 'success',
            'reference' => 'NGF-123456-ABCDEF',
            'amount'    => 10000,
        ],
    ]));

    $mockClient = m::mock(Client::class);
    $mockClient->shouldReceive('get')
               ->once()
               ->andReturn($mockResponse);

    $driver = new PaystackDriver($this->config);

    $reflection = new ReflectionClass($driver);
    $property   = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($driver, $mockClient);

    $result = $driver->verifyTransaction('NGF-123456-ABCDEF');

    expect($result)->toBeArray()
        ->and($result['status'])->toBeTrue()
        ->and($result['data']['status'])->toBe('success');
});

// -------------------------------------------------------
// resolveAccount
// -------------------------------------------------------

it('resolves a bank account successfully', function () {
    $mockResponse = new Response(200, [], json_encode([
        'status'  => true,
        'message' => 'Account number resolved',
        'data'    => [
            'account_name'   => 'SEYI AJIBOLA',
            'account_number' => '0123456789',
            'bank_id'        => 9,
        ],
    ]));

    $mockClient = m::mock(Client::class);
    $mockClient->shouldReceive('get')
               ->once()
               ->andReturn($mockResponse);

    $driver = new PaystackDriver($this->config);

    $reflection = new ReflectionClass($driver);
    $property   = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($driver, $mockClient);

    $result = $driver->resolveAccount('0123456789', '058');

    expect($result)->toBeArray()
        ->and($result['status'])->toBeTrue()
        ->and($result['data'])->toHaveKey('account_name')
        ->and($result['data']['account_name'])->toBe('SEYI AJIBOLA');
});

// -------------------------------------------------------
// Reference generation
// -------------------------------------------------------

it('generates a unique reference with NGF prefix', function () {
    $driver = new PaystackDriver($this->config);

    $reflection = new ReflectionClass($driver);
    $method     = $reflection->getMethod('generateReference');
    $method->setAccessible(true);

    $ref1 = $method->invoke($driver);
    $ref2 = $method->invoke($driver);

    expect($ref1)->toStartWith('NGF-')
        ->and($ref2)->toStartWith('NGF-')
        ->and($ref1)->not->toBe($ref2); // always unique
});