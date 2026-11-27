<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class ApplySellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->seller === null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'iban' => strtoupper(str_replace(' ', '', (string) $this->input('iban', ''))),
            'tax_number' => trim((string) $this->input('tax_number', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'min:3', 'max:255', 'unique:sellers,store_name'],
            'tax_number' => ['required', 'string', 'min:10', 'max:20', 'unique:sellers,tax_number'],
            'iban' => ['required', 'string', 'min:15', 'max:34', 'regex:/^TR[0-9]{24}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'iban.regex' => 'IBAN alanı TR ile başlamalı ve toplam 26 karakter olmalıdır (TR + 24 rakam).',
        ];
    }
}
