<?php
namespace App\Modules\Employees\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterEmployeesRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
      return [
          'tenant_id' => ['nullable','string'],
          'status'    => ['nullable','in:active,inactive'],
          'search'    => ['nullable','string','max:100'],
          'page'      => ['nullable','integer','min:1'],
          'per_page'  => ['nullable','integer','min:1','max:100'],
      ];
  }
}