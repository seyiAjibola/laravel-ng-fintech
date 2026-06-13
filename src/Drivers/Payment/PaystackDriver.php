<?php

namespace SeyiAjibola\NgFintech\Drivers\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SeyiAjibola\NgFintech\Contracts\PaymentProvider;
use SeyiAjibola\NgFintech\Exceptions\FintechException;

class PaystackDriver implements PaymentProvider
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        // Boot the HTTP client once — reused for all calls
        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    /**
     * Initialize a Paystack transaction.
     * Amount MUST be in kobo. Never pass raw naira.
     * e.g. NGN 500 = 50000 kobo
     */
    public function initializeTransaction(array $data): array
    {
        $this->validate($data, ['email', 'amount']);

        return $this->post('/transaction/initialize', [
            'email'     => $data['email'],
            'amount'    => $data['amount'], // kobo
            'reference' => $data['reference'] ?? $this->generateReference(),
            'callback_url' => $data['callback_url'] ?? null,
            'metadata'  => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Verify a transaction by reference.
     * Always verify server-side — never trust client-side confirmation.
     */
    public function verifyTransaction(string $reference): array
    {
        return $this->get("/transaction/verify/{$reference}");
    }

    /**
     * Get list of all banks in Nigeria.
     */
    public function listBanks(): array
    {
        return $this->get('/bank?currency=NGN&country=nigeria');
    }

    /**
     * Resolve a bank account number to get account name.
     * Use this before any transfer — confirm the account exists.
     */
    public function resolveAccount(string $accountNumber, string $bankCode): array
    {
        return $this->get("/bank/resolve?account_number={$accountNumber}&bank_code={$bankCode}");
    }

    /**
     * Initiate a transfer to a bank account.
     * Amount in kobo.
     */
    public function transfer(array $data): array
    {
        $this->validate($data, ['amount', 'recipient', 'reason']);

        return $this->post('/transfer', [
            'source'    => 'balance',
            'amount'    => $data['amount'], // kobo
            'recipient' => $data['recipient'],
            'reason'    => $data['reason'],
            'reference' => $data['reference'] ?? $this->generateReference(),
        ]);
    }

    // -------------------------------------------------------
    // HTTP Helpers — all Paystack calls go through these
    // -------------------------------------------------------

    protected function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->client->post($endpoint, [
                'json' => $data,
            ]);

            return $this->parse($response);

        } catch (GuzzleException $e) {
            throw new FintechException(
                "Paystack POST [{$endpoint}] failed: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    protected function get(string $endpoint): array
    {
        try {
            $response = $this->client->get($endpoint);

            return $this->parse($response);

        } catch (GuzzleException $e) {
            throw new FintechException(
                "Paystack GET [{$endpoint}] failed: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Parse Paystack response into a consistent array.
     * Every Paystack response has: status, message, data
     */
    protected function parse($response): array
    {
        $body = json_decode($response->getBody()->getContents(), true);

        if (! $body['status']) {
            throw new FintechException(
                "Paystack error: " . ($body['message'] ?? 'Unknown error')
            );
        }

        return [
            'status'  => true,
            'message' => $body['message'] ?? 'success',
            'data'    => $body['data'] ?? [],
        ];
    }

    /**
     * Simple required field validator.
     * Throws early before hitting the API with bad data.
     */
    protected function validate(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new FintechException(
                    "Paystack: [{$field}] is required but missing."
                );
            }
        }
    }

    /**
     * Generate a unique transaction reference.
     * Format: NGF-{timestamp}-{random}
     * NGF = NgFintech
     */
    protected function generateReference(): string
    {
        return 'NGF-' . time() . '-' . strtoupper(substr(uniqid(), -6));
    }
}