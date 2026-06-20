<?php

namespace SeyiAjibola\NgFintech\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    protected $table = 'ngfintech_webhooks';

    protected $fillable = [
        'driver',
        'event',
        'transaction_id',
        'payload',
        'signature',
        'verified',
        'status',
        'error_message',
        'ip_address',
    ];

    protected $casts = [
        'payload'  => 'array',
        'verified' => 'boolean',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeForDriver($query, string $driver)
    {
        return $query->where('driver', $driver);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function markAsProcessed(): self
    {
        $this->update(['status' => 'processed']);
        return $this;
    }

    public function markAsFailed(string $reason): self
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $reason,
        ]);
        return $this;
    }

    public function markAsIgnored(): self
    {
        $this->update(['status' => 'ignored']);
        return $this;
    }
}