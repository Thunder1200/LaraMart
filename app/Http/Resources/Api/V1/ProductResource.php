<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status?->value,
            'average_rating' => $this->average_rating,
            'seller' => [
                'id' => $this->seller?->id,
                'store_name' => $this->seller?->store_name,
                'slug' => $this->seller?->slug,
            ],
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ],
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
