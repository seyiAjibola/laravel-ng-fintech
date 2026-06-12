<?php

namespace SeyiAjibola\NgFintech\Contracts;

interface IdentityProvider
{
    /**
     * Verify a Bank Verification Number (BVN).
     */
    public function verifyBvn(string $bvn): array;

    /**
     * Verify a National Identification Number (NIN).
     */
    public function verifyNin(string $nin): array;

    /**
     * Lookup account details linked to a BVN.
     */
    public function lookupBvn(string $bvn): array;
}