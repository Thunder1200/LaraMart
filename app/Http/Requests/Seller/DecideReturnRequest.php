<?php

namespace App\Http\Requests\Seller;

use App\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ReturnRequest|null $returnRequest */
        $returnRequest = $this->route('returnRequest');

        return $returnRequest !== null && ($this->user()?->can('decide', $returnRequest) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
        ];
    }
}
