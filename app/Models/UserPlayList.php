<?php

namespace App\Models;

use App\Models\User;
use App\Models\PlayList;
use Illuminate\Database\Eloquent\Model;

class UserPlayList extends Model
{
    protected $table = 'user_playlists';

    protected $fillable = [
        'id_user',
        'id_playlist'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function playlist()
    {
        return $this->belongsTo(PlayList::class, 'id_playlist');
    }
}
