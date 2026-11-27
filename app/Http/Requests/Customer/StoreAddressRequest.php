<?php

namespace App\Http\Requests\Customer;

use App\Support\TurkeyCities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $cities = TurkeyCities::all();
        $selectedCity = (string) $this->input('city', '');
        $allowedDistricts = $cities[$selectedCity] ?? [];

        return [
            'title' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', Rule::in(array_keys($cities))],
            'district' => ['required', 'string', Rule::in($allowedDistricts)],
            'full_address' => ['required', 'string', 'min:10', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
