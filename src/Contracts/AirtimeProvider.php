<?php

namespace SeyiAjibola\NgFintech\Contracts;

interface AirtimeProvider
{
    /**
     * Purchase airtime for a phone number.
     *
     * $data must include: phone, network, amount
     * Networks: mtn, glo, airtel, 9mobile
     */
    public function purchaseAirtime(array $data): array;

    /**
     * Purchase a data bundle.
     *
     * $data must include: phone, network, plan_id
     */
    public function purchaseData(array $data): array;

    /**
     * Get available data plans for a network.
     */
    public function getDataPlans(string $network): array;
}