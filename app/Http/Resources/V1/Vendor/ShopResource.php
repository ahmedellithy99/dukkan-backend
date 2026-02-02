<?php

namespace App\Http\Resources\V1\Vendor;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
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
            'whatsapp_number' => $this->whatsapp_number,
            'phone_number' => $this->phone_number,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Include relationships when loaded
            'location' => $this->whenLoaded('location', function () {
                return new LocationResource($this->location);
            }),

            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'email' => $this->owner->email,
                ];
            }),

            'products_count' => $this->whenCounted('products'),
            
            'products' => $this->whenLoaded('products', function () {
                return ProductResource::collection($this->products);
            }),

            // Logo media using MediaResource
            'logo' => $this->whenLoaded('media', function () {
                $logo = $this->getFirstMedia('logo');
                return $logo ? new MediaResource($logo) : null;
            }),

            'media_count' => $this->whenCounted('media'),
        ];
    }
}