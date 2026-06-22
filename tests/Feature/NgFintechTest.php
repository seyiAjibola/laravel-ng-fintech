<?php

use SeyiAjibola\NgFintech\Facades\NgFintech;
use SeyiAjibola\NgFintech\Exceptions\FintechException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;

afterEach(function () {
    m::close();
});

it('resolves NgFintech payment manager from facade', function () {
    $manager = NgFintech::payment();

    expect($manager)->toBeInstanceOf(
        \SeyiAjibola\NgFintech\Services\FintechManager::class
    );
});

it('throws exception when disabled driver is called', function () {
    config()->set('fintech.payment.drivers.paystack.enabled', false);

    NgFintech::payment()->initializeTransaction([
        'email'  => 'test@example.com',
        'amount' => 10000,
    ]);
})->throws(\InvalidArgumentException::class);

it('throws exception when unsupported driver is called', function () {
    config()->set('fintech.payment.default', 'unsupported_driver');
    config()->set('fintech.payment.drivers.unsupported_driver.enabled', true);

    NgFintech::payment()->initializeTransaction([
        'email'  => 'test@example.com',
        'amount' => 10000,
    ]);
})->throws(\InvalidArgumentException::class);