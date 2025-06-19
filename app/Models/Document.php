<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RequestDocument;
use App\Models\CertificateLog;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'description',
        'status'
    ];

    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class, 'document');
    }

    public function certificateLogs()
    {
        return $this->hasMany(CertificateLog::class, 'document');
    }
}
