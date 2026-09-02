<?php

namespace App\Http\Requests;

class RegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:admin,vendor,customer',
            'shop_name' => 'nullable|required_if:role,vendor|string|max:150',
        ];
    }
}
