<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'note',
        'changed_at'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
