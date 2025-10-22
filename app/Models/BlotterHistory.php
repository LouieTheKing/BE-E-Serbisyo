<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlotterHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'status',
        'updated_by',
        'notes',
    ];

    /**
     * Get the blotter that this history belongs to
     */
    public function blotter()
    {
        return $this->belongsTo(Blotter::class, 'case_number', 'case_number');
    }

    /**
     * Get the user who updated the status
     */
    public function updatedBy()
    {
        return $this->belongsTo(Account::class, 'updated_by');
    }
}
