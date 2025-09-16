<?php
class UpdateEmployeeRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
      return [
          'first_name' => ['required','string','max:80'],
          'last_name'  => ['nullable','string','max:80'],
          'email'      => ['nullable','email:rfc,dns','max:150'],
          'phone'      => ['nullable','string','max:30'],
          'hire_date'  => ['nullable','date'],
      ];
  }
}