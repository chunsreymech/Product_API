<?php

namespace App\Http\Requests;

class StoreProductImageRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'path' => 'required|string',
            'is_primary' => 'nullable|boolean',
        ];
    }
}
