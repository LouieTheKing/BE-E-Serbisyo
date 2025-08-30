<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'remarks',
        'category',
        'rating'
    ];

    protected $table = "feedbacks";

    public function account()
    {
        return $this->belongsTo(Account::class, 'user');
    }
}
