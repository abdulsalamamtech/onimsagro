<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'product_type_id' => 'nullable|exists:product_types,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'banner_id' => 'nullable|exists:assets,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $this->route('product'),   
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'tag' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
