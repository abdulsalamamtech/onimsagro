<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
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
            'full_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:15',
            'email' => 'sometimes|email|max:255',
            'consultation_time' => 'required|date|after:now',
            'description' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,confirmed,completed,canceled',
        ];
    }
}
