<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the media resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getUrl(),
            
            'large_url' => $this->hasGeneratedConversion('large')
            ? $this->getUrl('large')
            : $this->getUrl(),

            'thumb_url' => $this->hasGeneratedConversion('thumb') 
                ? $this->getUrl('thumb') 
                : $this->getUrl(),
        ];
    }
}