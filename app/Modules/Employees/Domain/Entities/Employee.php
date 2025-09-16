<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\Entities;

use App\Modules\Employees\Domain\ValueObjects\{DocumentId, Email, Phone, EmployeeStatus};

final class Employee
{
    public function __construct(
        private string $id,
        private ?string $tenantId,
        private string $firstName,
        private ?string $lastName,
        private DocumentId $document,
        private ?Email $email,
        private ?Phone $phone,
        private ?\DateTimeImmutable $hireDate,
        private EmployeeStatus $status,
        private ?string $createdBy,
        private ?string $updatedBy,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt = null,
    ) {}

    // Fábrica estática de creación “segura”
    public static function create(
        string $id,
        ?string $tenantId,
        string $firstName,
        ?string $lastName,
        DocumentId $document,
        ?Email $email,
        ?Phone $phone,
        ?\DateTimeImmutable $hireDate,
        ?string $createdBy
    ): self {
        if (trim($firstName) === '') {
            throw new \InvalidArgumentException('first_name requerido');
        }
        return new self(
            id: $id,
            tenantId: $tenantId,
            firstName: $firstName,
            lastName: $lastName,
            document: $document,
            email: $email,
            phone: $phone,
            hireDate: $hireDate,
            status: EmployeeStatus::Active,
            createdBy: $createdBy,
            updatedBy: $createdBy,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
    }

    public function update(
        string $firstName,
        ?string $lastName,
        ?Email $email,
        ?Phone $phone,
        ?\DateTimeImmutable $hireDate,
        ?string $updatedBy
    ): void {
        if (trim($firstName) === '') {
            throw new \InvalidArgumentException('first_name requerido');
        }
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email     = $email;
        $this->phone     = $phone;
        $this->hireDate  = $hireDate;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(?string $updatedBy): void {
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters simples (puedes generar los que uses)
    public function id(): string { return $this->id; }
    public function tenantId(): ?string { return $this->tenantId; }
    public function firstName(): string { return $this->firstName; }
    public function lastName(): ?string { return $this->lastName; }
    public function document(): DocumentId { return $this->document; }
    public function email(): ?Email { return $this->email; }
    public function phone(): ?Phone { return $this->phone; }
    public function hireDate(): ?\DateTimeImmutable { return $this->hireDate; }
    public function status(): EmployeeStatus { return $this->status; }
}