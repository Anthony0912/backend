<?php

namespace App\Models;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

class UserVideo extends Model
{
    protected $table = 'user_videos';

    protected $fillable = [
        'id_user',
        'id_video'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'id_video');
    }
}
