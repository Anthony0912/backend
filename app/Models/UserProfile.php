<?php

namespace App\Models;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'id_user',
        'id_profile'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'id_profile');
    }
}
