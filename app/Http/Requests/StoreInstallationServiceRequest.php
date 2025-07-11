<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstallationServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'farm_size' => 'required|string|max:50',
            'installation_type_id' => 'nullable|exists:installation_types,id',
            'farm_location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,approved,rejected,completed',
        ];
    }
}
