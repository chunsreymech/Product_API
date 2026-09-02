<?php

namespace App\Http\Requests;

class UpdateAddressRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'label' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|required|string|max:500',
            'city' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
