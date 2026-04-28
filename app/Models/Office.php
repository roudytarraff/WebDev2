<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'address_id',
        'name',
        'contact_email',
        'contact_phone',
        'status'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
