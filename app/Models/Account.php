<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\RequestDocument;
use App\Models\Blotter;
use App\Models\Feedback;
use App\Models\CertificateLog;
use App\Models\ActivityLog;

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
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
        'civil_status'
    ];

    protected $hidden = [
        'password',
    ];

    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class, 'requestor');
    }

    public function blotters()
    {
        return $this->hasMany(Blotter::class, 'reporter');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'user');
    }

    public function certificateLogs()
    {
        return $this->hasMany(CertificateLog::class, 'requestor');
    }

    public function staffCertificateLogs()
    {
        return $this->hasMany(CertificateLog::class, 'staff');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'account');
    }

    public function accountProof()
    {
        return $this->hasOne(AccountProof::class, 'account_id');
    }
}
