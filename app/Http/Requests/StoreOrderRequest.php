<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
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
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Reject the order if any product does not have enough stock right now.
     * (Stock is only reduced once the order is paid; see PaymentConfirmationService.)
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Add up the quantity per product, in case a product appears on several lines.
                $requested = [];
                foreach ($this->input('order_items', []) as $index => $item) {
                    $productId = (int) $item['product_id'];
                    $requested[$productId] ??= ['quantity' => 0, 'index' => $index];
                    $requested[$productId]['quantity'] += (int) $item['quantity'];
                }

                $products = Product::whereIn('id', array_keys($requested))->get()->keyBy('id');
                foreach ($requested as $productId => $line) {
                    $product = $products->get($productId);
                    if (! $product || $line['quantity'] <= $product->stock) {
                        continue;
                    }

                    $validator->errors()->add(
                        'order_items.'.$line['index'].'.quantity',
                        $product->stock > 0
                            ? "Only {$product->stock} of {$product->name} left in stock."
                            : "{$product->name} is out of stock."
                    );
                }
            },
        ];
    }
}
