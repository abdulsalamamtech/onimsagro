<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'product_type_id' => 'required|exists:product_types,id',
            'product_category_id' => 'required|exists:product_categories,id',
            'banner_id' => 'nullable|exists:assets,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'tag' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'required|in:active,inactive',
        ];
    }
}
