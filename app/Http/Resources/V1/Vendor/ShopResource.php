<?php

namespace App\Http\Resources\V1\Vendor;

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

            // Media information
            'logo' => $this->whenLoaded('media', function () {
                $logo = $this->getFirstMedia('logo');
                if (!$logo) {
                    return null;
                }
                
                $logoData = [
                    'id' => $logo->id,
                    'name' => $logo->name,
                    'file_name' => $logo->file_name,
                    'mime_type' => $logo->mime_type,
                    'size' => $logo->size,
                    'url' => $logo->getUrl(), // Original: logo.webp
                ];

                // Add thumbnail URL if conversion exists
                // This will be: logo-thumb.webp
                if ($logo->hasGeneratedConversion('thumb')) {
                    $logoData['thumb_url'] = $logo->getUrl('thumb');
                } else {
                    // Fallback to main URL if no thumbnail
                    $logoData['thumb_url'] = $logoData['url'];
                }

                return $logoData;
            }),

            'media_count' => $this->whenCounted('media'),
        ];
    }
}