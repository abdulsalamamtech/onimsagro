<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmerRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:farmers,email'],
            'phone_number' => ['required', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:100'],
            'type_of_farming_id' => ['required', 'exists:type_of_farmings,id'],
            'farm_name' => ['nullable', 'string', 'max:255'],
            'farm_size' => ['nullable', 'numeric', 'min:0.001'],
            'farm_size_unit' => ['nullable', 'in:acres,hectares,plots'],
            'main_products' => ['nullable', 'string', 'max:255'],
            'do_you_own_farming_equipment' => ['required', 'in:yes,no'],
            'where_do_you_sell_your_products' => ['nullable', 'string'],
            'challenge_in_selling_your_products' => ['nullable', 'string'],
            'additional_comment' => ['nullable', 'string'],
            // 'country' => ['nullable', 'string', 'max:100'],
        ];
    }
}
