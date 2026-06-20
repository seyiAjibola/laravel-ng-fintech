<?php

namespace SeyiAjibola\NgFintech\Services;

use SeyiAjibola\NgFintech\Models\Transaction;
use SeyiAjibola\NgFintech\Models\TransactionError;

class TransactionLogger
{
    /**
     * Start a transaction log before hitting the API.
     * Returns the Transaction model so we can update it after.
     */
    public function begin(
        string $category,
        string $driver,
        string $action,
        array  $requestPayload = [],
        string $reference = null
    ): Transaction {
        return Transaction::create([
            'category'        => $category,
            'driver'          => $driver,
            'action'          => $action,
            'reference'       => $reference,
            'status'          => 'pending',
            'request_payload' => $requestPayload,
            'ip_address'      => request()?->ip(),
            'user_agent'      => request()?->userAgent(),
        ]);
    }

    /**
     * Mark transaction as successful after API responds.
     */
    public function success(
        Transaction $transaction,
        array       $response = [],
        string      $providerReference = null
    ): Transaction {
        $transaction->update([
            'status'              => 'success',
            'response_payload'    => $response,
            'provider_reference'  => $providerReference,
        ]);

        return $transaction;
    }

    /**
     * Mark transaction as failed and log the error.
     */
    public function failed(
        Transaction $transaction,
        string      $errorMessage,
        string      $errorCode = null,
        array       $response = []
    ): Transaction {
        $transaction->update([
            'status'           => 'failed',
            'error_message'    => $errorMessage,
            'error_code'       => $errorCode,
            'response_payload' => $response,
        ]);

        return $transaction;
    }

    /**
     * Log a raw error that happened before or outside a transaction.
     */
    public function logError(
        string $category,
        string $driver,
        string $action,
        string $errorMessage,
        array  $requestPayload = [],
        string $errorCode = null,
        string $stackTrace = null,
        Transaction $transaction = null
    ): TransactionError {
        return TransactionError::create([
            'category'        => $category,
            'driver'          => $driver,
            'action'          => $action,
            'transaction_id'  => $transaction?->id,
            'error_code'      => $errorCode,
            'error_message'   => $errorMessage,
            'stack_trace'     => $stackTrace,
            'request_payload' => $requestPayload,
            'ip_address'      => request()?->ip(),
            'environment'     => app()->environment(),
        ]);
    }

    /**
     * Check if logging is enabled in config.
     */
    public function isEnabled(): bool
    {
        return config('fintech.logging.enabled', true);
    }
}