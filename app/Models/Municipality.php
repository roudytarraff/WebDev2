<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'address_id',
        'status'
    ];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}