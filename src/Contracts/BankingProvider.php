<?php

namespace SeyiAjibola\NgFintech\Contracts;

interface BankingProvider
{
    /**
     * Create a virtual/reserved bank account for a customer.
     */
    public function createVirtualAccount(array $data): array;

    /**
     * Get details of an existing virtual account.
     */
    public function getVirtualAccount(string $accountReference): array;

    /**
     * Get transaction history for a virtual account.
     */
    public function getTransactions(string $accountReference, array $filters = []): array;
}