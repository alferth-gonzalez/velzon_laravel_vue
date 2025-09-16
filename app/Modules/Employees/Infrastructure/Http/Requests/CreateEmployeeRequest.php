<?php
namespace App\Modules\Employees\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest {
    public function authorize(): bool { return true; } // usa Policies si quieres
    public function rules(): array {
        return [
            'tenant_id'      => ['nullable','string'],
            'first_name'     => ['required','string','max:80'],
            'last_name'      => ['nullable','string','max:80'],
            'document_type'  => ['required','in:CC,NIT,CE,PA,TI,RC'],
            'document_number'=> ['required','string','max:32'],
            'email'          => ['nullable','email:rfc,dns','max:150'],
            'phone'          => ['nullable','string','max:30'],
            'hire_date'      => ['nullable','date'],
        ];
    }
}