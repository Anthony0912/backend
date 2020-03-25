<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FactorAuthentication extends Model
{
    protected $table = 'factor_authentications';

    protected $fillable = [
        'id_user',
        'id_verify'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }

}
