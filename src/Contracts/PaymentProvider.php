<?php

namespace SeyiAjibola\NgFintech\Contracts;

interface PaymentProvider
{
    /**
     * Initialize a new transaction.
     * Amount must always be in kobo (NGN lowest unit).
     * Never pass raw naira floats.
     */
    public function initializeTransaction(array $data): array;

    /**
     * Verify a transaction by its reference.
     */
    public function verifyTransaction(string $reference): array;

    /**
     * Fetch list of supported banks.
     */
    public function listBanks(): array;

    /**
     * Resolve a bank account — get account name from number + bank code.
     */
    public function resolveAccount(string $accountNumber, string $bankCode): array;

    /**
     * Initiate a transfer to a bank account.
     */
    public function transfer(array $data): array;
}