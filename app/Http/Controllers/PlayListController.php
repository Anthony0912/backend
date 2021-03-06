<?php

namespace App\Http\Controllers;

use App\Models\PlayList;
use App\Models\UserPlayList;
use App\Models\UserProfile;
use App\Models\VideoPlayList;
use App\Models\UserVideo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayListController extends Controller
{

    public function playlistShow($id)
    {
        $playlists = UserPlayList::join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where('user_playlists.id_user', '=', $id)
            ->get();
        $playlists = $this->disabledPlaylistGeneral($playlists);
        return response()->json($playlists, Response::HTTP_OK);
    }

    public function videoPlaylistCreate(Request $request)
    {
        $user_video = UserVideo::where([
            'id_user' => $request->id_user,
            'id_video' => $request->id_video
        ])->first();
        $user_playlist = UserPlayList::where([
            'id_user' => $request->id_user,
            'id_playlist' => $request->id_playlist
        ])->first();
        if ($video_playlist = $this->findVideoInPlayList([
            'id_user_video' => $user_video->id,
            'id_user_playlist' => $user_playlist->id
        ])) {
            $video_playlist->delete();
            return response()->json([
                'error' => 'Delete video of playlist',
                'add_playlist' => false
            ], Response::HTTP_OK);
        } else {
            VideoPlayList::create([
                'id_user_video' => $user_video->id,
                'id_user_playlist' => $user_playlist->id
            ]);
            return response()->json([
                'error' => 'Add playlist',
                'add_playlist' => true
            ], Response::HTTP_OK);
        }
        return response()->json([
            'error' => 'No action was taken',
            'add_playlist' => false
        ], Response::HTTP_NOT_FOUND);
    }

    public function videoPlaylist($id_user, $id_playlist)
    {
        $user_video_play = VideoPlayList::join('user_videos', 'user_videos.id', '=', 'video_playlists.id_user_video')
            ->join('user_playlists', 'user_playlists.id', '=', 'video_playlists.id_user_playlist')
            ->join('videos', 'videos.id', '=', 'user_videos.id_video')
            ->join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where(['user_videos.id_user' => $id_user, 'user_playlists.id_user' => $id_user])
            ->get();
        $object = $this->sortPlayListWithVideos($user_video_play, $id_playlist);
        return response()->json($object, Response::HTTP_OK);
    }

    private function objectUserVideoPlayList($id_user, $video, $playlist)
    {
        return [
            'id_user' => $id_user,
            'video' => $video,
            'playlist' => $playlist,
        ];
    }

    private function sortPlayListWithVideos($user_video_play, $id_playlist)
    {
        $object = [];
        $id_videos = [];
        foreach ($user_video_play as $value) {
            if (!$this->isIdVideo($id_videos, $value->id_video)) {
                array_push($object, $this->objectUserVideoPlayList(
                    $value->id_user,
                    $this->sortVideos($value->id_video, $value->name_video, $value->url, $value->status),
                    $this->findPlayLists($user_video_play, $value->id_video, $id_playlist)
                ));
                array_push($id_videos, $value->id_video);
            }
        }
        return $object;
    }

    private function isIdVideo($id_videos, $id)
    {
        for (
            $i = 0;
            $i < count($id_videos);
            $i++
        ) {
            if ($id_videos[$i] === $id) {
                return true;
            }
        }
        return false;
    }

    private function findPlayLists($user_video_play, $id_video, $id_playlist)
    {
        $object = [];
        foreach ($user_video_play as $value) {
            if ($value->id_video === $id_video && $id_playlist == $value->id_playlist) {
                array_push($object, ['id_playlist' => $value->id_playlist, 'name_playlist' => $value->name_playlist]);
            }
        }
        return $object;
    }

    private function sortVideos($id_video, $name_video, $url, $status)
    {
        return [
            'id_video' => $id_video,
            'name_video' => $name_video,
            'url' => $url,
            'status' => $status,
        ];
    }
    
    private function findVideoInPlayList($video_playlist)
    {
        return VideoPlayList::where($video_playlist)->first();
    }

    private function disabledPlaylistGeneral($playlists)
    {
        $temp = [];
        foreach ($playlists as $value) {
            if ($value->name_playlist != 'General') {
                array_push($temp, $value);
            }
        }
        return $temp;
    }

    public function playlistCreate(Request $request)
    {
        if ($request->name_playlist === 'General') {
            return response()->json(['error' => ['name_playlist' => 'Name playlist exists, it is not possible to use it.']], Response::HTTP_NOT_FOUND);
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
            return response()->json(['error' => ['name_playlist' => 'Name playlist exists, it is not possible to use it.']], Response::HTTP_NOT_FOUND);
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

    public function showPlaylistInProfile($id)
    {
        $playlists = UserProfile::join('user_playlists', 'user_playlists.id_user', '=', 'user_profiles.id_user')
            ->join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where('user_profiles.id_profile', '=', $id)
            ->get();
        $object = $this->getPlaylistProfile($playlists);
        return response()->json($object, Response::HTTP_OK);
    }
    public function getVideosSearch($id, $search)
    {
        $videos = UserProfile::join('user_videos', 'user_videos.id_user', '=', 'user_profiles.id_user')
            ->join('videos', 'videos.id', '=', 'user_videos.id_video')
            ->select('videos.id', 'videos.name_video', 'videos.url')
            ->where('videos.name_video', 'ilike', '%' . $search . '%')
            ->where('videos.status', '=', true)
            ->where('user_profiles.id_profile', '=', $id)
            ->get();
        $object = $this->findUrl($videos);
        return response()->json($object, Response::HTTP_OK);
    }

    private function findUrl($object){
        foreach ($object as $value) {
            $value->url =$value->url;
        }
        return $object;
    }

    private function getPlaylistProfile($playlists)
    {
        $object = [];
        foreach ($playlists as $value) {
            if ($value->name_playlist != 'General') {
                array_push($object, ['id' => $value->id_playlist, 'name' => $value->name_playlist]);
            }
        }
        return $object;
    }

    public function getVideosInProfile($id)
    {
        $videos = UserPlayList::join('video_playlists', 'video_playlists.id_user_playlist', '=', 'user_playlists.id')
            ->join('user_videos', 'user_videos.id', '=', 'video_playlists.id_user_video')
            ->join('videos', 'videos.id', '=', 'user_videos.id_video')
            ->where(['user_playlists.id_playlist' => $id, 'videos.status' => true])
            ->get();
        $object = $this->sortVideosProfile($videos);
        return response()->json($object, Response::HTTP_OK);
    }

    public function sortVideosProfile($videos)
    {
        $object = [];
        foreach ($videos as $value) {
            array_push($object, $this->sortVideos($value->id, $value->name_video, $value->url, $value->status));
        }
        return $object;
    }
}
