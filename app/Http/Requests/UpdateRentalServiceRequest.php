<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalServiceRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:255'],
            'farm_size' => ['nullable', 'numeric', 'min:0.001'],
            'farm_size_unit' => ['nullable', 'in:acres,hectares,plots'],
            'equipment_type_id' => ['required', 'exists:equipment_types,id'],
            'renting_purpose' => ['required', 'string', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:100'],
            'duration' => ['required', 'integer', 'min:1'],
            // duration unit enum of days, weeks, months, years
            'duration_unit' => ['required', 'string', 'in:days,weeks,months,years'],
            'amount' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:2000'], // by admin
            'status' => ['required', 'in:pending,approved,rejected,completed'], // by admin
        ];
    }
}
