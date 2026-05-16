<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'request_id',
        'citizen_user_id',
        'office_id',
        'rating',
        'comment',
        'office_reply',
    ];

    protected $casts = [
        'rating' => 'integer',
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
}