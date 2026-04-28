<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitizenProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'national_id_number',
        'date_of_birth',
        'id_document_path',
        'verification_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}