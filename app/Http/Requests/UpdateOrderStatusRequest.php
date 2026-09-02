<?php

namespace App\Http\Requests;

class UpdateOrderStatusRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ];
    }
}
