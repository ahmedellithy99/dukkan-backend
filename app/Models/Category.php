<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug, Filterable;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(30)
            ->usingSeparator('-');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    /**
     * Check if category can be deleted (no products in subcategories)
     */
    public function canBeDeleted()
    {
        return $this->subcategories()
            ->whereHas('products')
            ->count() === 0;
    }
}
