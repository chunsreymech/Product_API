<?php

namespace App\Http\Requests;

class StoreCategoryRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
