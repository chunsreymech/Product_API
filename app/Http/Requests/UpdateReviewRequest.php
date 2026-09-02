<?php

namespace App\Http\Requests;

class UpdateReviewRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
