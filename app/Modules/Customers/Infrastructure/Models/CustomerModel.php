<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerModel extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'type',
        'document_type',
        'document_number',
        'business_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'segment',
        'notes',
        'blacklist_reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContactModel::class, 'customer_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddressModel::class, 'customer_id');
    }

    public function taxProfile(): HasOne
    {
        return $this->hasOne(CustomerTaxProfileModel::class, 'customer_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CustomerAuditLogModel::class, 'customer_id');
    }

    public function getFullNameAttribute(): string
    {
        if ($this->type === 'natural') {
            return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        }

        return $this->business_name;
    }

    public function getFormattedDocumentAttribute(): string
    {
        return $this->document_type . ':' . $this->document_number;
    }

    public function scopeByTenant($query, ?string $tenantId)
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', '%' . $search . '%')
              ->orWhere('first_name', 'like', '%' . $search . '%')
              ->orWhere('last_name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('document_number', 'like', '%' . $search . '%');
        });
    }
}

