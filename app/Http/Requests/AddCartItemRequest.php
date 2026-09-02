<?php

namespace App\Http\Requests;

class AddCartItemRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }
}
