<?php

namespace App\Http\Requests\PubgAccount;

use Illuminate\Foundation\Http\FormRequest;

class StorePubgAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pubg_id' => ['required', 'string', 'min:5', 'max:14', 'regex:/^5\d{4,13}$/'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}