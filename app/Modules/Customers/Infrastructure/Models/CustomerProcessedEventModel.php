<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProcessedEventModel extends Model
{
    protected $table = 'customer_processed_events';

    public $timestamps = false;

    protected $fillable = [
        'idempotency_key',
        'event_type',
        'payload',
        'result',
        'processed_at',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'processed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeByType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }
}
