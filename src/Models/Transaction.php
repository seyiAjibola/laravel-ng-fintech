<?php

namespace SeyiAjibola\NgFintech\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use SoftDeletes;

    protected $table = 'ngfintech_transactions';

    protected $fillable = [
        'category',
        'driver',
        'action',
        'reference',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'request_payload',
        'response_payload',
        'metadata',
        'error_message',
        'error_code',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'metadata'         => 'array',
        'amount'           => 'integer',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    /**
     * The model that owns this transaction.
     * Could be a User, Business, Wallet — anything.
     *
     * Usage: $transaction->transactable → returns the User
     */
    public function transactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Webhooks received for this transaction.
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * Errors that occurred on this transaction.
     */
    public function errors(): HasMany
    {
        return $this->hasMany(TransactionError::class);
    }

    // -------------------------------------------------------
    // Scopes — for clean querying
    // -------------------------------------------------------

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForDriver($query, string $driver)
    {
        return $query->where('driver', $driver);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Amount in Naira — always store in kobo, display in naira.
     */
    public function getAmountInNairaAttribute(): float
    {
        return $this->amount / 100;
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsSuccess(array $response = []): self
    {
        $this->update([
            'status'           => 'success',
            'response_payload' => $response,
        ]);

        return $this;
    }

    public function markAsFailed(string $reason, array $response = []): self
    {
        $this->update([
            'status'           => 'failed',
            'error_message'    => $reason,
            'response_payload' => $response,
        ]);

        return $this;
    }
}