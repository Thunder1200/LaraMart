<?php

namespace App\Http\Requests\Customer;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $orderItemId = $this->input('order_item_id');
        if (! is_numeric($orderItemId)) {
            return false;
        }

        $orderItem = OrderItem::query()->with('subOrder.order')->find((int) $orderItemId);

        return $orderItem !== null && ($this->user()?->can('create', [Review::class, $orderItem]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Lütfen 1 ile 5 arasında bir puan seçin.',
            'rating.min' => 'Puan en az 1 yıldız olmalıdır.',
            'rating.max' => 'Puan en fazla 5 yıldız olmalıdır.',
            'comment.required' => 'Lütfen ürün hakkındaki yorumunuzu yazın.',
            'comment.min' => 'Yorumunuz en az 10 karakter olmalıdır.',
        ];
    }
}
