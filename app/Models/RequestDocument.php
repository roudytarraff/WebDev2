<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'required_document_id',
        'uploaded_by_user_id',
        'file_name',
        'file_path',
        'file_type',
        'document_role',
        'uploaded_at'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function requiredDocument()
    {
        return $this->belongsTo(ServiceRequiredDocument::class, 'required_document_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}