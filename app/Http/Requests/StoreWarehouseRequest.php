<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
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
            'banner' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'tag' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'created_by' => 'required|exists:users,id',
            // 'banner_id' => 'nullable|exists:assets,id',
            // 'sku' => 'nullable|string|max:255|unique:warehouses,sku',

        ];
    }
}
