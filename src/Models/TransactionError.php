<?php

namespace SeyiAjibola\NgFintech\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionError extends Model
{
    protected $table = 'ngfintech_errors';

    protected $fillable = [
        'category',
        'driver',
        'action',
        'transaction_id',
        'error_code',
        'error_message',
        'stack_trace',
        'request_payload',
        'response_payload',
        'ip_address',
        'environment',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
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

    public function scopeForDriver($query, string $driver)
    {
        return $query->where('driver', $driver);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}