<?php
namespace App\Modules\Employees\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeCollection extends ResourceCollection {
    public function toArray($request): array {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total() ?? null,
            ],
        ];
    }
}