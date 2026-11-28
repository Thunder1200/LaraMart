<?php

namespace App\Http\Requests\Customer;

use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var OrderItem|null $orderItem */
        $orderItem = $this->route('orderItem');

        return $orderItem !== null && ($this->user()?->can('create', [ReturnRequest::class, $orderItem]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Lütfen iade nedeninizi açıklayın.',
            'reason.min' => 'İade nedeni en az 10 karakter olmalıdır.',
        ];
    }
}
