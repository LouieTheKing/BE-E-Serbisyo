<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blotter extends Model
{
    use HasFactory;

    protected $fillable = [
        'blotter_number',
        'status',
        'remarks',
        'incidents',
        'location',
        'incident_date',
        'reporter'
    ];

    protected $casts = [
        'incident_date' => 'date'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'reporter');
    }
}
