<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customers.create');
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['natural', 'juridical'])],
            'document_type' => ['required', 'string', Rule::in(['CC', 'NIT', 'CE', 'PA', 'TI', 'RC'])],
            'document_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9\.\-]+$/',
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('tenant_id', $this->input('tenant_id'))
                                 ->where('document_type', $this->input('document_type'));
                })
            ],
            'business_name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('tenant_id', $this->input('tenant_id'));
                })
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]{7,20}$/'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'suspended', 'blacklisted', 'prospect'])],
            'segment' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de cliente es obligatorio.',
            'type.in' => 'El tipo de cliente debe ser natural o jurídica.',
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.unique' => 'Ya existe un cliente con este documento en el tenant.',
            'document_number.regex' => 'El número de documento solo puede contener números, puntos y guiones.',
            'business_name.required' => 'La razón social es obligatoria.',
            'business_name.max' => 'La razón social no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Ya existe un cliente con este email en el tenant.',
            'phone.regex' => 'El teléfono debe tener un formato válido.',
            'status.required' => 'El estado del cliente es obligatorio.',
            'status.in' => 'El estado del cliente no es válido.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que persona natural tenga nombres
            if ($this->input('type') === 'natural') {
                if (empty($this->input('first_name')) && empty($this->input('last_name'))) {
                    $validator->errors()->add('first_name', 'Una persona natural debe tener al menos nombre o apellido.');
                }
            }

            // Validar tipos de documento según tipo de cliente
            $validDocuments = [
                'natural' => ['CC', 'CE', 'PA', 'TI', 'RC'],
                'juridical' => ['NIT']
            ];

            $type = $this->input('type');
            $documentType = $this->input('document_type');

            if ($type && $documentType && !in_array($documentType, $validDocuments[$type], true)) {
                $validator->errors()->add('document_type', 
                    "El tipo de documento {$documentType} no es válido para {$type}."
                );
            }

            // Validar NIT si es el tipo de documento
            if ($documentType === 'NIT') {
                $documentNumber = preg_replace('/[^0-9]/', '', $this->input('document_number', ''));
                if (strlen($documentNumber) < 8) {
                    $validator->errors()->add('document_number', 'El NIT debe tener al menos 8 dígitos.');
                }
            }

            // Validar que tenga al menos un medio de contacto
            if (empty($this->input('email')) && empty($this->input('phone'))) {
                $validator->errors()->add('email', 'El cliente debe tener al menos un email o teléfono.');
            }
        });
    }
}
