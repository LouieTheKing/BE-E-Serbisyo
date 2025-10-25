<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'position',
        'term_start',
        'term_end',
        'status'
    ];

    protected $casts = [
        'term_start' => 'date',
        'term_end' => 'date'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
