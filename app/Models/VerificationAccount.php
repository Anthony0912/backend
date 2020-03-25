<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class VerificationAccount extends Model
{
    protected $table = 'verification_accounts';

    protected $fillable = [
        'id_user',
        'email',
        'activated'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }

}
