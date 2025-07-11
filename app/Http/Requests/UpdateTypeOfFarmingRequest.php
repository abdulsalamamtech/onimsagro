<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeOfFarmingRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:32', 'unique:type_of_farmings,name'],
            // 'name' => ['required', 'string', 'max:32', 'unique:type_of_farmings,name,' . $this->route('typeOfFarming')],
            // 'name' => ['required', 'string', 'max:32', 'unique:type_of_farmings,name,' . $this->route('type_of_farming')->id],
        ];
    }
}
