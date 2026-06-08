<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uid'                  => ['required', 'string', 'min:5', 'max:14', 'regex:/^5\d{4,13}$/'],
            'product_id'           => ['nullable', 'integer', 'exists:products,id'],
            'cart'                 => ['nullable', 'array', 'min:1'],
            'cart.*.product_id'    => ['required_with:cart', 'integer', 'exists:products,id'],
            'cart.*.quantity'      => ['required_with:cart', 'integer', 'min:1', 'max:100'],
            'cart.*.price'         => ['nullable', 'numeric', 'min:1'],
            'qty'                  => ['nullable', 'integer', 'min:1', 'max:100'],
            'email'                => ['nullable', 'email', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $hasProduct = $this->filled('product_id');
            $hasCart    = is_array($this->input('cart')) && count($this->input('cart')) > 0;

            if (!$hasProduct && !$hasCart) {
                $v->errors()->add('product_id', 'Нужен product_id или cart[].');
            }
        });
    }

    public function messages(): array
    {
        return [
            'uid.required' => 'Player ID является обязательным.',
            'uid.regex'    => 'Player ID должен начинаться с 5 и содержать 5-14 цифр.',
            'uid.min'      => 'Player ID слишком короткий (минимум 5 цифр).',
            'uid.max'      => 'Player ID слишком длинный (максимум 14 цифр).',
        ];
    }
}