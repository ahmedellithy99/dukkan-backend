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
        'address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = [
        'full_address',
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
}
