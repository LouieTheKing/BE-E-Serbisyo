<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request',
        'staff',
        'remark'
    ];

    public function documentRequest()
    {
        return $this->belongsTo(RequestDocument::class, 'document_request');
    }

    public function staffAccount()
    {
        return $this->belongsTo(Account::class, 'staff');
    }
}
