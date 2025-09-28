<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class UploadedDocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploader',
        'document',
        'requirement',
        'file_path'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'uploader');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document');
    }
    public function requirement()
    {
        return $this->belongsTo(DocumentRequirement::class, 'requirement');
    }

}
