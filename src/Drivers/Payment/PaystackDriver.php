<?php

namespace SeyiAjibola\NgFintech\Drivers\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SeyiAjibola\NgFintech\Contracts\PaymentProvider;
use SeyiAjibola\NgFintech\Exceptions\FintechException;
use SeyiAjibola\NgFintech\Services\TransactionLogger;

class PaystackDriver implements PaymentProvider
{
    protected Client $client;
    protected array $config;
    protected TransactionLogger $logger;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->logger = app(TransactionLogger::class);

        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    public function initializeTransaction(array $data): array
    {
        $this->validate($data, ['email', 'amount']);

        $reference = $data['reference'] ?? $this->generateReference();

        // Begin log before hitting API
        $transaction = null;
        if ($this->logger->isEnabled()) {
            $transaction = $this->logger->begin(
                category: 'payment',
                driver: 'paystack',
                action: 'initializeTransaction',
                requestPayload: $data,
                reference: $reference,
            );
        }

        try {
            $result = $this->post('/transaction/initialize', [
                'email'        => $data['email'],
                'amount'       => $data['amount'],
                'reference'    => $reference,
                'callback_url' => $data['callback_url'] ?? null,
                'metadata'     => $data['metadata'] ?? [],
            ]);

            // Log success
            if ($this->logger->isEnabled() && $transaction) {
                $this->logger->success(
                    transaction: $transaction,
                    response: $result,
                    providerReference: $result['data']['reference'] ?? null,
                );
            }

            return $result;

        } catch (FintechException $e) {
            // Log failure
            if ($this->logger->isEnabled() && $transaction) {
                $this->logger->failed(
                    transaction: $transaction,
                    errorMessage: $e->getMessage(),
                    errorCode: (string) $e->getCode(),
                );
            }

            throw $e;
        }
    }

    public function verifyTransaction(string $reference): array
    {
        $transaction = null;
        if ($this->logger->isEnabled()) {
            $transaction = $this->logger->begin(
                category: 'payment',
                driver: 'paystack',
                action: 'verifyTransaction',
                requestPayload: ['reference' => $reference],
                reference: $reference,
            );
        }

        try {
            $result = $this->get("/transaction/verify/{$reference}");

            if ($this->logger->isEnabled() && $transaction) {
                $status = $result['data']['status'] ?? 'pending';
                $status === 'success'
                    ? $this->logger->success($transaction, $result)
                    : $this->logger->failed($transaction, 'Transaction not successful', null, $result);
            }

            return $result;

        } catch (FintechException $e) {
            if ($this->logger->isEnabled() && $transaction) {
                $this->logger->failed($transaction, $e->getMessage(), (string) $e->getCode());
            }

            throw $e;
        }
    }

    public function listBanks(): array
    {
        return $this->get('/bank?currency=NGN&country=nigeria');
    }

    public function resolveAccount(string $accountNumber, string $bankCode): array
    {
        return $this->get("/bank/resolve?account_number={$accountNumber}&bank_code={$bankCode}");
    }

    public function transfer(array $data): array
    {
        $this->validate($data, ['amount', 'recipient', 'reason']);

        $reference = $data['reference'] ?? $this->generateReference();

        $transaction = null;
        if ($this->logger->isEnabled()) {
            $transaction = $this->logger->begin(
                category: 'payment',
                driver: 'paystack',
                action: 'transfer',
                requestPayload: $data,
                reference: $reference,
            );
        }

        try {
            $result = $this->post('/transfer', [
                'source'    => 'balance',
                'amount'    => $data['amount'],
                'recipient' => $data['recipient'],
                'reason'    => $data['reason'],
                'reference' => $reference,
            ]);

            if ($this->logger->isEnabled() && $transaction) {
                $this->logger->success($transaction, $result);
            }

            return $result;

        } catch (FintechException $e) {
            if ($this->logger->isEnabled() && $transaction) {
                $this->logger->failed($transaction, $e->getMessage(), (string) $e->getCode());
            }

            throw $e;
        }
    }

    // -------------------------------------------------------
    // HTTP Helpers
    // -------------------------------------------------------

    protected function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->client->post($endpoint, ['json' => $data]);
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

    protected function generateReference(): string
    {
        return 'NGF-' . time() . '-' . strtoupper(substr(uniqid(), -6));
    }
}