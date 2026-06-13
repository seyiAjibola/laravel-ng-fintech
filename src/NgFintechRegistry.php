<?php

namespace SeyiAjibola\NgFintech;

use SeyiAjibola\NgFintech\Services\FintechManager;

class NgFintechRegistry
{
    /**
     * Return the payment manager.
     * Supports: paystack, flutterwave, monnify
     *
     * Usage:
     * NgFintech::payment()->initializeTransaction([...])
     * NgFintech::payment()->driver('flutterwave')->initializeTransaction([...])
     */
    public function payment(): FintechManager
    {
        return app('fintech.payment');
    }

    /**
     * Return the airtime manager.
     * Supports: vtpass, clubkonnect, nellobytes
     *
     * Usage:
     * NgFintech::airtime()->purchaseAirtime([...])
     */
    public function airtime(): FintechManager
    {
        return app('fintech.airtime');
    }

    /**
     * Return the bills manager.
     * Supports: vtpass, buypower, baxi
     *
     * Usage:
     * NgFintech::bills()->payElectricity([...])
     * NgFintech::bills()->payCableTv([...])
     */
    public function bills(): FintechManager
    {
        return app('fintech.bills');
    }

    /**
     * Return the identity manager.
     * Supports: mono, okra, prembly
     *
     * Usage:
     * NgFintech::identity()->verifyBvn($bvn)
     * NgFintech::identity()->verifyNin($nin)
     */
    public function identity(): FintechManager
    {
        return app('fintech.identity');
    }

    /**
     * Return the banking manager.
     * Supports: sterling
     *
     * Usage:
     * NgFintech::banking()->createVirtualAccount([...])
     */
    public function banking(): FintechManager
    {
        return app('fintech.banking');
    }
}