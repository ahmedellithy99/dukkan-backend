<?php

namespace App\Http\Resources\V1\Vendor;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
            'final_price' => $this->getFinalPrice(),
            'stock_quantity' => $this->stock_quantity,
            'track_stock' => $this->track_stock,
            'in_stock' => $this->track_stock ? $this->stock_quantity > 0 : true,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Shop information
            'shop' => $this->whenLoaded('shop', function () {
                return [
                    'id' => $this->shop->id,
                    'name' => $this->shop->name,
                    'slug' => $this->shop->slug,
                ];
            }),

            // Category information
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

            // Product images using MediaResource
            'images' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getMedia('images'));
            }),

            'images_count' => $this->whenCounted('media'),

            // Attribute values
            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->groupBy('attribute.name')->map(function ($values, $attributeName) {
                    return [
                        'name' => $attributeName,
                        'values' => $values->pluck('value')->toArray(),
                    ];
                })->values();
            }),

            // Product statistics
            'stats' => $this->whenLoaded('stats', function () {
                return [
                    'views_count' => $this->stats->views_count ?? 0,
                    'whatsapp_clicks' => $this->stats->whatsapp_clicks ?? 0,
                    'favorites_count' => $this->stats->favorites_count ?? 0,
                ];
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