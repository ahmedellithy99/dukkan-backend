<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'views_count' => $this->views_count,
            'whatsapp_clicks' => $this->whatsapp_clicks,
            'favorites_count' => $this->favorites_count,
            'last_viewed_at' => $this->last_viewed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
