<?php

namespace App\Http\Requests;

use App\Support\ValidationMessages;
use Illuminate\Foundation\Http\FormRequest;

class ServiceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi oleh middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'motor' => ['required', 'string', 'max:100'],
            'serviced_at' => ['required', 'date_format:Y-m-d'],
            'odometer_km' => ['required', 'integer', 'min:0', 'max:9999999'],
            'service_type' => ['required', 'string', 'max:100'],
            'cost' => ['required', 'integer', 'min:0', 'max:999999999'],
            'workshop' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ValidationMessages::messages();
    }

    public function attributes(): array
    {
        return ValidationMessages::attributes();
    }
}
