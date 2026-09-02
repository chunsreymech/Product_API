<?php

namespace App\Http\Requests;

class CreateOrderRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'shipping_address' => 'required_without:shipping_address_id|nullable|string',
            'payment_method' => 'nullable|in:cash_on_delivery,demo_card,bank_transfer',
            'notes' => 'nullable|string',
        ];
    }
}
