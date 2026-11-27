<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_number' => preg_replace('/\D+/', '', (string) $this->input('card_number', '')),
            'expire_month' => str_pad(trim((string) $this->input('expire_month', '')), 2, '0', STR_PAD_LEFT),
            'expire_year' => trim((string) $this->input('expire_year', '')),
            'cvc' => trim((string) $this->input('cvc', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', $userId),
            ],
            'idempotency_key' => ['required', 'string', 'min:10', 'max:100'],
            'distance_sales_agreement' => ['accepted'],
            'card_holder' => ['required', 'string', 'min:3', 'max:120'],
            'card_number' => ['required', 'digits:16'],
            'expire_month' => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
            'expire_year' => ['required', 'digits:4'],
            'cvc' => ['required', 'digits_between:3,4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'distance_sales_agreement.accepted' => 'Devam etmek için Mesafeli Satış Sözleşmesini onaylamanız gerekir.',
            'card_number.digits' => 'Kart numarası 16 rakamdan oluşmalıdır.',
            'expire_month.regex' => 'Son kullanma ayı 01 ile 12 arasında olmalıdır.',
        ];
    }
}
