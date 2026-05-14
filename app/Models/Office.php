<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'address_id',
        'name',
        'contact_email',
        'contact_phone',
        'status'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function staff()
    {
        return $this->hasMany(OfficeStaff::class);
    }

    public function workingHours()
    {
        return $this->hasMany(OfficeWorkingHour::class);
    }

    public function serviceCategories()
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class);
    }

    public function documentTypes()
    {
        return $this->hasMany(DocumentType::class);
    }
}
