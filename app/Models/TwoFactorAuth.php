<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'code_or_secret',
        'is_enabled'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}