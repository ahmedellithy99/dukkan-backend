<?php

namespace App\Http\Resources\V1\Admin;

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
            'id' => $this->id,
            'title' => $this->title,
            'display_order' => $this->display_order,

            // Media
            'carousel_image' => $this->when(
                $this->hasMedia('carousel_image'),
                fn() => new MediaResource($this->getFirstMedia('carousel_image'))
            ),
        ];
    }
}
