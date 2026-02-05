<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'views_count' => $this->views_count,
            'whatsapp_clicks' => $this->whatsapp_clicks,
            'sms_clicks' => $this->sms_clicks,
            'favorites_count' => $this->favorites_count,
            'last_viewed_at' => $this->last_viewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}