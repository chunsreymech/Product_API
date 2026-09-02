<?php

namespace App\Http\Requests;

class UpdateCartItemRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1',
        ];
    }
}
