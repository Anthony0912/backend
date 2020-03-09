<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorAuthentication extends Model
{
    protected $table = 'factor_authentications';

    protected $fillable = [
        'id_user',
        'id_verify'
    ];
}
