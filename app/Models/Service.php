<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'category_id',
        'name',
        'description',
        'instructions',
        'price',
        'duration_minutes',
        'requires_appointment',
        'supports_online_payment',
        'supports_crypto_payment',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function documents()
    {
        return $this->hasMany(ServiceRequiredDocument::class);
    }
}