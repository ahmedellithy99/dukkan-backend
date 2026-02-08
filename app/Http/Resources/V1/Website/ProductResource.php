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
            'has_discount' => $this->hasDiscount(),

            'shop' => $this->whenLoaded('shop', function () {
                return new ShopResource($this->shop);
            }),

            'attribute_values' => $this->whenLoaded('attributeValues', function () {
                return AttributeValueResource::collection($this->attributeValues);
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
