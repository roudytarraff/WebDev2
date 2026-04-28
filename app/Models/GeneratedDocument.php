<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'document_type',
        'file_path',
        'generated_at'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}