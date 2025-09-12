<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Entities;

use Carbon\Carbon;

class TaxProfile
{
    public function __construct(
        private ?int $id,
        private int $customerId,
        private string $taxRegime,
        private array $taxResponsibilities = [],
        private array $activityCodes = [],
        private ?string $taxAddress = null,
        private bool $isRetentionAgent = false,
        private bool $isSelfRetainer = false,
        private ?string $notes = null,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {
        $this->validateTaxRegime($taxRegime);
        $this->createdAt = $createdAt ?? Carbon::now();
        $this->updatedAt = $updatedAt ?? Carbon::now();
    }

    public static function create(
        int $customerId,
        string $taxRegime,
        array $taxResponsibilities = [],
        array $activityCodes = [],
        ?string $taxAddress = null,
        bool $isRetentionAgent = false,
        bool $isSelfRetainer = false,
        ?string $notes = null
    ): self {
        return new self(
            id: null,
            customerId: $customerId,
            taxRegime: $taxRegime,
            taxResponsibilities: $taxResponsibilities,
            activityCodes: $activityCodes,
            taxAddress: $taxAddress ? trim($taxAddress) : null,
            isRetentionAgent: $isRetentionAgent,
            isSelfRetainer: $isSelfRetainer,
            notes: $notes,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
    }

    private function validateTaxRegime(string $regime): void
    {
        $validRegimes = [
            'simplified',
            'common',
            'special',
            'no_responsible',
            'great_contributor'
        ];

        if (!in_array($regime, $validRegimes, true)) {
            throw new \InvalidArgumentException(
                'Régimen tributario inválido. Regímenes válidos: ' . implode(', ', $validRegimes)
            );
        }
    }

    public function update(
        string $taxRegime,
        array $taxResponsibilities = [],
        array $activityCodes = [],
        ?string $taxAddress = null,
        bool $isRetentionAgent = false,
        bool $isSelfRetainer = false,
        ?string $notes = null
    ): void {
        $this->validateTaxRegime($taxRegime);
        
        $this->taxRegime = $taxRegime;
        $this->taxResponsibilities = $taxResponsibilities;
        $this->activityCodes = $activityCodes;
        $this->taxAddress = $taxAddress ? trim($taxAddress) : null;
        $this->isRetentionAgent = $isRetentionAgent;
        $this->isSelfRetainer = $isSelfRetainer;
        $this->notes = $notes;
        $this->updatedAt = Carbon::now();
    }

    public function addTaxResponsibility(string $responsibility): void
    {
        if (!in_array($responsibility, $this->taxResponsibilities, true)) {
            $this->taxResponsibilities[] = $responsibility;
            $this->updatedAt = Carbon::now();
        }
    }

    public function removeTaxResponsibility(string $responsibility): void
    {
        $this->taxResponsibilities = array_values(
            array_filter($this->taxResponsibilities, fn($r) => $r !== $responsibility)
        );
        $this->updatedAt = Carbon::now();
    }

    public function addActivityCode(string $code): void
    {
        if (!in_array($code, $this->activityCodes, true)) {
            $this->activityCodes[] = $code;
            $this->updatedAt = Carbon::now();
        }
    }

    public function removeActivityCode(string $code): void
    {
        $this->activityCodes = array_values(
            array_filter($this->activityCodes, fn($c) => $c !== $code)
        );
        $this->updatedAt = Carbon::now();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'tax_regime' => $this->taxRegime,
            'tax_responsibilities' => $this->taxResponsibilities,
            'activity_codes' => $this->activityCodes,
            'tax_address' => $this->taxAddress,
            'is_retention_agent' => $this->isRetentionAgent,
            'is_self_retainer' => $this->isSelfRetainer,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getTaxRegime(): string { return $this->taxRegime; }
    public function getTaxResponsibilities(): array { return $this->taxResponsibilities; }
    public function getActivityCodes(): array { return $this->activityCodes; }
    public function getTaxAddress(): ?string { return $this->taxAddress; }
    public function isRetentionAgent(): bool { return $this->isRetentionAgent; }
    public function isSelfRetainer(): bool { return $this->isSelfRetainer; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
}
