<?php
// EmployeeResource.php
namespace App\Modules\Employees\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hire_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}