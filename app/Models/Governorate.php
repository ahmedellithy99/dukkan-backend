<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    // Relationships
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    // Scopes
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    // Helper methods
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
