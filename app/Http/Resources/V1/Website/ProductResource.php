<?php

namespace App\Http\Resources\V1\Website;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array for public website.
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
            'discounted_price' => $this->when($this->hasDiscount(), $this->getDiscountedPrice()),
            'savings_amount' => $this->when($this->hasDiscount(), $this->getSavingsAmount()),
            'stock_quantity' => $this->stock_quantity,
            'is_in_stock' => $this->isInStock(),
            'is_low_stock' => $this->isLowStock(),
            'has_discount' => $this->hasDiscount(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'shop' => $this->whenLoaded('shop', function () {
                return new ShopResource($this->shop);
            }),

            'subcategory' => $this->whenLoaded('subcategory', function () {
                return new SubcategoryResource($this->subcategory);
            }),

            'attribute_values' => $this->whenLoaded('attributeValues', function () {
                return AttributeValueResource::collection($this->attributeValues);
            }),

            'stats' => $this->whenLoaded('stats', function () {
                return [
                    'views_count' => $this->stats->views_count ?? 0,
                    'whatsapp_clicks_count' => $this->stats->whatsapp_clicks_count ?? 0,
                    'favorites_count' => $this->stats->favorites_count ?? 0,
                ];
            }),

            'main_image' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getMedia('main_image'));
            }),

            'secondary_image' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->getMedia('secondary_image'));
            }),
        ];
    }
}
