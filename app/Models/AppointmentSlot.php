<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'service_id',
        'slot_date',
        'start_time',
        'end_time',
        'capacity',
        'status'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'slot_id');
    }
}
