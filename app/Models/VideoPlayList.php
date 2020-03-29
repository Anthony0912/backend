<?php

namespace App\Models;

use App\Models\UserVideo;
use App\Models\UserPlayList;
use Illuminate\Database\Eloquent\Model;

class VideoPlayList extends Model
{
    protected $table = 'video_playlists';

    protected $fillable = [
        'id_user_video',
        'id_user_playlist'
    ];

    public function userVideo()
    {
        return $this->belongsTo(UserVideo::class, 'id_user_video');
    }

    public function userPlaylist()
    {
        return $this->belongsTo(UserPlayList::class, 'id_user_playlist');
    }
}
