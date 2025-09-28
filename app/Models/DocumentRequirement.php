<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class DocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'document'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document');
    }
}
