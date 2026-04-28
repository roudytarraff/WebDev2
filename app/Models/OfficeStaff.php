<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeStaff extends Model
{
    use HasFactory;


    
    protected $fillable = [
        'office_id',
        'user_id',
        'job_title',
        'status'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}