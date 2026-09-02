<?php

namespace App\Http\Requests;

class StoreReviewRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
