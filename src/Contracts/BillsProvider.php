<?php

namespace SeyiAjibola\NgFintech\Contracts;

interface BillsProvider
{
    /**
     * Verify a meter number or smartcard number before payment.
     * Prevents paying to a wrong/invalid meter.
     */
    public function verifyMeter(string $meterNumber, string $meterType, string $disco): array;

    /**
     * Pay an electricity bill.
     *
     * $data must include: meter_number, amount, disco, meter_type (prepaid/postpaid)
     */
    public function payElectricity(array $data): array;

    /**
     * Verify a cable TV smartcard/IUC number.
     */
    public function verifySmartcard(string $smartcardNumber, string $provider): array;

    /**
     * Pay a cable TV subscription.
     *
     * $data must include: smartcard_number, provider, plan_id
     * Providers: dstv, gotv, startimes
     */
    public function payCableTv(array $data): array;

    /**
     * Get available cable TV plans for a provider.
     */
    public function getCableTvPlans(string $provider): array;
}