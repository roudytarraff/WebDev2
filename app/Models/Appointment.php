<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'citizen_user_id',
        'office_id',
        'slot_id',
        'status',
        'notes'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_user_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function slot()
    {
        return $this->belongsTo(AppointmentSlot::class, 'slot_id');
    }
}