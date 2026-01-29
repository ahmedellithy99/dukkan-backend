<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'area',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    // Scopes
    public function scopeInCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeByArea($query, $area)
    {
        return $query->where('area', $area);
    }

    public function scopeNearby($query, $lat, $lng, $radius = 10)
    {
        return $query->selectRaw('
            *, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) + sin(radians(?)) *
            sin(radians(latitude)))) AS distance
        ', [$lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radius])
            ->orderBy('distance');
    }

    // Helper methods
    public function getFullAddressAttribute()
    {
        $address = $this->city->name . ', ' . $this->city->governorate->name;

        if ($this->area) {
            $address = $this->area . ', ' . $address;
        }

        return $address;
    }

    public function getGovernorateAttribute()
    {
        return $this->city->governorate;
    }

    /**
     * Get city from subdomain for multi-tenant architecture
     */
    public static function getCityFromSubdomain($subdomain)
    {
        return City::where('slug', $subdomain)->first();
    }
}
