<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedAccount extends Model
{
    use HasFactory;

    protected $table = 'rejected_accounts';

    protected $fillable = [
        'reason',
        'email',
        'password',
        'status',
        'type',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'nationality',
        'birthday',
        'contact_no',
        'birth_place',
        'municipality',
        'barangay',
        'house_no',
        'zip_code',
        'street',
        'pwd_number',
        'single_parent_number',
        'profile_picture_path',
    ];
}
