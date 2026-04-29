<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auths';

    protected $fillable = [
        'user_id',
        'otp_hash',
        'expires_at'
    ];
}