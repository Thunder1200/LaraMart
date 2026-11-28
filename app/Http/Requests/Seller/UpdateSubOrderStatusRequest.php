<?php

namespace App\Http\Requests\Seller;

use App\Enums\SubOrderStatus;
use App\Models\SubOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SubOrder|null $subOrder */
        $subOrder = $this->route('subOrder');

        return $subOrder !== null && ($this->user()?->can('update', $subOrder) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(SubOrderStatus::class)],
            'cargo_company' => ['nullable', 'required_if:status,shipped', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'required_if:status,shipped', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Lütfen yeni sipariş durumunu seçin.',
            'cargo_company.required_if' => 'Kargoya verildi durumuna geçmek için kargo firmasını seçmelisiniz.',
            'tracking_number.required_if' => 'Kargoya verildi durumuna geçmek için kargo takip numarası girmelisiniz.',
        ];
    }
}
