<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'requestor',
        'document',
        'status',
        'information'
    ];

    protected $with = ['documentDetails', 'account'];

    protected $casts = [
        'information' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'requestor');
    }

    public function documentDetails()
    {
        return $this->belongsTo(Document::class, 'document');
    }

    public function uploadedRequirements()
    {
        return $this->hasMany(UploadedDocumentRequirement::class, 'request_document_id', 'id');
    }

    public function certificateLogs()
    {
        return $this->hasMany(CertificateLog::class, 'document_request');
    }
}
