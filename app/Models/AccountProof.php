<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'back_id_card',
        'front_id_card',
        'selfie_id_card',
        'account_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
