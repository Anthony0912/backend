<?php

namespace App\Models;

use App\Models\User;
use App\Models\Video;
use App\Models\PlayList;
use Illuminate\Database\Eloquent\Model;

class UserVideoPlayList extends Model
{
    protected $table = 'user_video_playlists';

    protected $fillable = [
        'id_user',
        'id_video',
        'id_playlist'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'id_video');
    }

    public function playlist()
    {
        return $this->belongsTo(PlayList::class, 'id_playlist');
    }
}
