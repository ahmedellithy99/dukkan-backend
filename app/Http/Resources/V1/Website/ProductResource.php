<?php

namespace App\Http\Resources\V1\Website;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array for public website consumption.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'final_price' => $this->getFinalPrice(), // Calculated price after discount
            'stock_quantity' => $this->track_stock ? $this->stock_quantity : null,
            'in_stock' => $this->track_stock ? $this->stock_quantity > 0 : true,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,

            // Shop information (minimal for public)
            'shop' => $this->whenLoaded('shop', function () {
                return [
                    'id' => $this->shop->id,
                    'name' => $this->shop->name,
                    'slug' => $this->shop->slug,
                    'whatsapp_number' => $this->shop->whatsapp_number,
                    'phone_number' => $this->shop->phone_number,
                ];
            }),

            'subcategory' => $this->whenLoaded('subcategory', function () {
                return [
                    'id' => $this->subcategory->id,
                    'name' => $this->subcategory->name,
                    'slug' => $this->subcategory->slug,
                    'category' => $this->subcategory->whenLoaded('category', function () {
                        return [
                            'id' => $this->subcategory->category->id,
                            'name' => $this->subcategory->category->name,
                            'slug' => $this->subcategory->category->slug,
                        ];
                    }),
                ];
            }),

            'images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getMedia('images'));
            }),

            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->groupBy('attribute.name')->map(function ($values, $attributeName) {
                    return [
                        'name' => $attributeName,
                        'values' => $values->pluck('value')->toArray(),
                    ];
                })->values();
            }),
        ];
    }

    /**
     * Calculate final price after discount
     */
    private function getFinalPrice(): ?float
    {
        if (!$this->price) {
            return null;
        }

        if (!$this->discount_value) {
            return $this->price;
        }

        if ($this->discount_type === 'percent') {
            return $this->price - ($this->price * ($this->discount_value / 100));
        }

        if ($this->discount_type === 'amount') {
            return max(0, $this->price - $this->discount_value);
        }

        return $this->price;
    }
}