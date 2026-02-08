<?php

namespace App\Http\Resources\V1\Website;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdCarouselResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'display_order' => $this->display_order,

            // Media
            'carousel_image' => $this->whenLoaded('media', function () {
                return MediaResource::collection($this->media);
            }),
        ];
    }
}
