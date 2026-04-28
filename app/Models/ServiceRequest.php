<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'citizen_user_id',
        'office_id',
        'service_id',
        'assigned_to_user_id',
        'status',
        'description',
        'qr_code',
        'submitted_at'
    ];

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_user_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(RequestStatusHistory::class);
    }
}
