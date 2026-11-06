<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'shipping' => $this->shipping,
            'total' => $this->total,
            'payment_status' => $this->payment_status?->value,
            'address' => $this->address,
            'created_at' => $this->created_at?->toIso8601String(),
            'sub_orders' => $this->whenLoaded('subOrders', function ()
            {
                return $this->subOrders->map(function ($sub)
                {
                    return [
                        'id' => $sub->id,
                        'seller_id' => $sub->seller_id,
                        'status' => $sub->status?->value,
                        'cargo_company' => $sub->cargo_company,
                        'tracking_number' => $sub->tracking_number,
                        'commission_amount' => $sub->commission_amount,
                        'items' => $sub->items->map(fn ($item) => [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                        ]),
                    ];
                });
            }),
        ];
    }
}

