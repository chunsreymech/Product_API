<?php

namespace App\Http\Requests;

class StoreProductRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lte:price',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'featured' => 'nullable|boolean',
        ];
    }
}
