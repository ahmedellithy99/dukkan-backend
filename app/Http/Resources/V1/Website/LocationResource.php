<?php

namespace App\Http\Resources\V1\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array for public website consumption.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'area' => $this->area,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            'city' => $this->whenLoaded('city', function () {
                return new CityResource($this->city);
            }),
        ];
    }
}