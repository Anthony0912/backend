<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayListRequest;
use App\Models\PlayList;
use App\Models\UserPlayList;
use App\Models\VideoPlayList;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayListController extends Controller
{

    public function playlistShow($id)
    {
        $playlists = UserPlayList::join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where('user_playlists.id_user' ,'=', $id)
            ->get();
        return response()->json($playlists, Response::HTTP_OK);
    }
    public function playlistCreate(PlayListRequest $request)
    {
        if ($request->name_playlist === 'General') {
            return response()->json(['error' => 'Name invalid'], Response::HTTP_NOT_FOUND);
        }
        $playlist = PlayList::create(['name_playlist' => $request->name_playlist]);
        UserPlayList::create(['id_user' => $request->id_user, 'id_playlist' => $playlist->id]);
        return response()->json(['error' => 'created playlist.'], Response::HTTP_OK);
    }

    public function playlistEdit($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        $playlist = PlayList::find($id);
        return response()->json($playlist, Response::HTTP_OK);
    }

    public function playlistUpdate(Request $request)
    {
        if ($request->name_playlist === 'General') {
            return response()->json(['error' => 'Name invalid'], Response::HTTP_NOT_FOUND);
        }

        if ($this->existsId($request->id)) {
            return response()->json(['error' => 'ID playlist not exist'], Response::HTTP_NOT_FOUND);
        }
        request()->validate([
            'name_playlist' => 'required',
        ]);

        $playlist = PlayList::find($request->id);
        $playlist->update(['name_playlist' => $request->name_playlist]);
        return response()->json(['error' => 'Playlist update'], Response::HTTP_OK);
    }

    public function playlistDelete($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        $userPlaylist = UserPlayList::where('id_playlist', $id)->first();
        VideoPlayList::where('id_user_playlist', $userPlaylist->id)->delete();
        $userPlaylist->delete();
        PlayList::find($id)->delete();
        return response()->json(['error' => 'Deleted video'], Response::HTTP_OK);
    }

    private function existsId($id)
    {
        $playlist = PlayList::find($id);
        return $playlist === null || empty($id);
    }

}
