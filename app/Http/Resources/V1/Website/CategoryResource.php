<?php

namespace App\Http\Resources\V1\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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

            // Include subcategories when loaded
            'subcategories' => $this->whenLoaded('subcategories', function () {
                return SubcategoryResource::collection($this->subcategories);
            }),
        ];
    }
}