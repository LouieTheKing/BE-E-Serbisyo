<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestor',
        'document',
        'status'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'requestor');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document');
    }
}
