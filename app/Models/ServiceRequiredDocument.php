<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'document_type_id',
        'document_name',
        'is_required'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
