<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestor',
        'staff',
        'document',
        'remark'
    ];

    public function requestorAccount()
    {
        return $this->belongsTo(Account::class, 'requestor');
    }

    public function staffAccount()
    {
        return $this->belongsTo(Account::class, 'staff');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document');
    }
}
