<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'min:3', 'max:150'],
            'tax_number' => ['required', 'string', 'min:10', 'max:20'],
            'iban' => ['required', 'string', 'min:16', 'max:34'],
        ];
    }
}
