<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blotter extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'complainant_name',
        'respondent_name',
        'case_type',
        'additional_respondent',
        'complaint_details',
        'relief_sought',
        'date_filed',
        'received_by',
        'created_by',
        'status',
    ];

    protected $casts = [
        'additional_respondent' => 'array',
        'date_filed' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(Account::class, 'created_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Account::class, 'received_by');
    }
}
