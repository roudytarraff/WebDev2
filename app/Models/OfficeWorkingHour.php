<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeWorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'weekday_number',
        'open_time',
        'close_time',
        'is_closed',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
