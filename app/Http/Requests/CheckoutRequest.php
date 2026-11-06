<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'terms_accepted' => ['accepted'],
            'idempotency_key' => ['required', 'string', 'max:100'],
            'card_holder_name' => ['nullable', 'string', 'max:100'],
            'card_number' => ['nullable', 'string', 'max:25'],
            'expire_month' => ['nullable', 'string', 'max:2'],
            'expire_year' => ['nullable', 'string', 'max:4'],
            'cvc' => ['nullable', 'string', 'max:4'],
            'simulate_failure' => ['nullable', 'boolean'],
        ];
    }
}
