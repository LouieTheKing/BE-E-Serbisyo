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
        'attached_proof',
    ];

    protected $casts = [
        'additional_respondent' => 'array',
        'date_filed' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['attached_proof_url'];

    /**
     * Get the full URL for the attached proof image
     */
    public function getAttachedProofUrlAttribute()
    {
        if ($this->attached_proof) {
            return asset('storage/' . $this->attached_proof);
        }
        return null;
    }

    public function creator()
    {
        return $this->belongsTo(Account::class, 'created_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    /**
     * Get the account that received this blotter
     */
    public function receivedBy()
    {
        return $this->belongsTo(Account::class, 'received_by');
    }

    /**
     * Get the status history for this blotter
     */
    public function statusHistory()
    {
        return $this->hasMany(BlotterHistory::class, 'case_number', 'case_number')
                    ->orderBy('created_at', 'desc');
    }
}
