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
        'submitted_at',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function documents()
    {
        return $this->hasMany(RequestDocument::class, 'request_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'request_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'request_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'request_id');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class, 'request_id');
    }

    public function generatedDocuments()
    {
        return $this->hasMany(GeneratedDocument::class, 'request_id');
    }
}