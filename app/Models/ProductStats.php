<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStats extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'views_count',
        'whatsapp_clicks',
        'sms_clicks',
        'favorites_count',
        'last_viewed_at',
        'updated_at',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'whatsapp_clicks' => 'integer',
        'sms_clicks' => 'integer',
        'favorites_count' => 'integer',
        'last_viewed_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
