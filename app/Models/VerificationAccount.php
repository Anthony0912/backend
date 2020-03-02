<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationAccount extends Model
{
    protected $table = 'verification_accounts';

    protected $fillable = [
        'id_user',
        'email',
        'activated'
    ];
}
