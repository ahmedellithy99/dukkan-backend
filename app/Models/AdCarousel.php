<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class AdCarousel extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'link_url',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('carousel_image')
            ->singleFile()
            ->useDisk('public');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1920, 1080)
            ->format('webp')
            ->quality(85)
            ->performOnCollections('carousel_image')
            ->nonQueued();
    }
}
