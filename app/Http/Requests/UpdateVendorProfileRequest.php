<?php

namespace App\Http\Requests;

class UpdateVendorProfileRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'shop_name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
        ];
    }
}
