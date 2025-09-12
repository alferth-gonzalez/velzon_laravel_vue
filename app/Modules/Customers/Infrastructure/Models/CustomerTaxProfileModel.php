<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTaxProfileModel extends Model
{
    protected $table = 'customer_tax_profiles';

    protected $fillable = [
        'customer_id',
        'tax_regime',
        'tax_responsibilities',
        'activity_codes',
        'tax_address',
        'is_retention_agent',
        'is_self_retainer',
        'notes',
    ];

    protected $casts = [
        'tax_responsibilities' => 'array',
        'activity_codes' => 'array',
        'is_retention_agent' => 'boolean',
        'is_self_retainer' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function scopeByRegime($query, string $regime)
    {
        return $query->where('tax_regime', $regime);
    }

    public function scopeRetentionAgents($query)
    {
        return $query->where('is_retention_agent', true);
    }

    public function scopeSelfRetainers($query)
    {
        return $query->where('is_self_retainer', true);
    }
}
