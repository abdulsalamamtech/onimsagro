<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicalSupportRequest extends FormRequest
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
        // Admins usually only change the status, so every field is optional.
        return [
            'full_name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|max:255',
            'crop_type_id' => 'sometimes|nullable|integer|exists:crop_types,id',
            'stage_of_plant' => 'sometimes|required|string|max:255',
            'problem_with_crop' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,solved,not_solved',
        ];
    }
}
