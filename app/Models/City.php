<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'governorate_id',
        'name',
        'slug',
    ];

    // Relationships
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    // Scopes
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeInGovernorate($query, $governorateId)
    {
        return $query->where('governorate_id', $governorateId);
    }

    // Helper methods
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getFullNameAttribute()
    {
        return $this->name . ', ' . $this->governorate->name;
    }
}
