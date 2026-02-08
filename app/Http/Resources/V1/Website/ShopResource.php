<?php

namespace App\Http\Resources\V1\Website;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array for public website consumption.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'whatsapp_number' => $this->whatsapp_number,
            'phone_number' => $this->phone_number,

            'location' => $this->whenLoaded('location', function () {
                return new LocationResource($this->location);
            }),

            'products' => $this->whenLoaded('products', function () {
                return ProductResource::collection($this->products->where('is_active', true));
            }),

            'logo' => $this->whenLoaded('media', function () {
                $logo = $this->getFirstMedia('logo');
                return $logo ? new MediaResource($logo) : null;
            }),
        ];
    }
}