<?php

namespace App\Http\Requests;

class ProcessPaymentRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'method' => 'required|in:cash_on_delivery,demo_card,bank_transfer',
            'card_number' => 'nullable|required_if:method,demo_card|string',
            'bank_reference' => 'nullable|required_if:method,bank_transfer|string',
        ];
    }
}
