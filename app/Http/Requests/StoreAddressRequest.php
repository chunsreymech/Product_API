<?php

namespace App\Http\Requests;

class StoreAddressRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'label' => 'nullable|string|max:50',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ];
    }
}
