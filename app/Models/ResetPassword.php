<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    protected $table = 'reset_passwords';

    protected $fillable = [
        'email',
        'id_verify'
    ];
}
