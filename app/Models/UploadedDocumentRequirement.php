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
        'file_path',
        'request_document_id'
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

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }
}
