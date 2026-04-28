<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'transaction_type',
        'provider_reference',
        'tx_hash',
        'status',
        'processed_at'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}