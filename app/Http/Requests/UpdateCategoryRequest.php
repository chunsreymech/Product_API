<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($categoryId)],
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive',
        ];
    }
}
