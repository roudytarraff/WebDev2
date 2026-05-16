<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'provider',
        'status',
        'transaction_reference',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_id');
    }

    public function successfulTransaction()
    {
        return $this->hasOne(PaymentTransaction::class, 'payment_id')
            ->where('status', 'success')
            ->latest('processed_at');
    }
}