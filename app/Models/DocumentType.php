<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'name',
        'description',
        'status',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function requiredDocuments()
    {
        return $this->hasMany(ServiceRequiredDocument::class);
    }
}
