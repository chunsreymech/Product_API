<?php

namespace App\Http\Requests;

class ApplyCouponRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:coupons,code',
        ];
    }
}
