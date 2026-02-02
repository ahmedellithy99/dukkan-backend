<?php

namespace App\Http\Resources\V1\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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

            'governorate' => $this->whenLoaded('governorate', function () {
                return new GovernorateResource($this->governorate);
            }),
        ];
    }
}