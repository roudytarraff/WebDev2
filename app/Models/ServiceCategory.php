<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'name',
        'description'
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
