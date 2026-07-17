<?php

namespace App\Http\Requests\PubgAccount;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePubgAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}