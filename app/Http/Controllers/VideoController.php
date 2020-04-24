<?php

namespace App\Http\Controllers;

use App\Models\PlayList;
use App\Models\UserPlayList;
use App\Models\UserVideo;
use App\Models\Video;
use App\Models\VideoPlayList;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoController extends Controller
{
    public function showVideos($id)
    {
        $user_video_play = VideoPlayList::join('user_videos', 'user_videos.id', '=', 'video_playlists.id_user_video')
            ->join('user_playlists', 'user_playlists.id', '=', 'video_playlists.id_user_playlist')
            ->join('videos', 'videos.id', '=', 'user_videos.id_video')
            ->join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where(['user_videos.id_user' => $id, 'user_playlists.id_user' => $id])
            ->get();
        $object = $this->sortPlayListWithVideos($user_video_play);
        return response()->json($object, Response::HTTP_OK);
    }

    public function getVideoSearch($id, $search)
    {
        $user_video_play = VideoPlayList::join('user_videos', 'user_videos.id', '=', 'video_playlists.id_user_video')
            ->join('user_playlists', 'user_playlists.id', '=', 'video_playlists.id_user_playlist')
            ->join('videos', 'videos.id', '=', 'user_videos.id_video')
            ->join('playlists', 'playlists.id', '=', 'user_playlists.id_playlist')
            ->where('videos.name_video', 'ilike', '%' . $search . '%')
            ->where('user_videos.id_user', '=', $id)
            ->where('user_playlists.id_user', '=', $id)
            ->get();
        $object = $this->sortPlayListWithVideos($user_video_play);
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

    private function sortPlayListWithVideos($user_video_play)
    {
        $object = [];
        $id_videos = [];
        foreach ($user_video_play as $value) {
            if (!$this->isIdVideo($id_videos, $value->id_video)) {
                array_push($object, $this->objectUserVideoPlayList(
                    $value->id_user,
                    $this->sortVideos($value->id_video, $value->name_video, $value->url, $value->status),
                    $this->findPlayLists($user_video_play, $value->id_video)
                ));
                array_push($id_videos, $value->id_video);
            }
        }
        return $object;
    }



    private function isIdVideo($id_videos, $id)
    {
        for ($i = 0; $i < count($id_videos); $i++) {
            if ($id_videos[$i] === $id) {
                return true;
            }
        }
        return false;
    }

    private function findPlayLists($user_video_play, $id_video)
    {
        $object = [];
        foreach ($user_video_play as $value) {
            if ($value->id_video === $id_video) {
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
            'url' => $this->getUrl($url),
            'status' => $status,
        ];
    }

    private function getUrl($url)
    {
        preg_match('/src="([^"]+)"/', $url, $match);
        return $match[1];
    }

    public function create(Request $request)
    {
        if (!$this->formatUrl($request->url)) {
            return response()->json(['error' => ['url' => 'Url invalid']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $video = Video::create($this->video($request));
        $userVideo = UserVideo::create(
            [
                'id_user' => $request->id_user,
                'id_video' => $video->id,
            ]
        );
        $this->createdGeneralPlayList($request, $userVideo);
        return response()->json(['error' => 'Created video'], Response::HTTP_CREATED);
    }

    private function createdGeneralPlayList($request, $userVideo)
    {
        if (!$this->existsGeneralPlayList($request->id_user)) {
            $playlist = PlayList::create(['name_playlist' => 'General']);
            $userPlaylist = UserPlayList::create(
                [
                    'id_user' => $request->id_user,
                    'id_playlist' => $playlist->id,
                ]
            );
            VideoPlayList::create(
                [
                    'id_user_video' => $userVideo->id,
                    'id_user_playlist' => $userPlaylist->id,
                ]
            );
        } else {
            $userPlaylist = UserPlayList::where('id_user', $request->id_user)->get();
            foreach ($userPlaylist as $value) {
                if ($value->playlist->name_playlist === 'General') {
                    VideoPlayList::create(
                        [
                            'id_user_video' => $userVideo->id,
                            'id_user_playlist' => $value->id,
                        ]
                    );
                    return;
                }
            }
        }
    }

    private function existsGeneralPlayList($id_user)
    {
        return UserPlayList::where('id_user', $id_user)->count() > 0;
    }

    private function video($request)
    {
        return [
            'name_video' => trim($request->name_video),
            'url' => $request->url,
            'status' => $request->status,
        ];
    }

    public function videoChangeStatus($id)
    {
        $video = Video::find($id);
        $video->status = $video->status ? false : true;
        $video->save();
        return response()->json(['status' => $video->status], Response::HTTP_OK);
    }

    public function videoEdit($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        $video = Video::find($id);
        return response()->json($video, Response::HTTP_OK);
    }

    public function videoUpdate(Request $request)
    {
        if ($this->existsId($request->id)) {
            return response()->json(['error' => 'ID video not exist'], Response::HTTP_NOT_FOUND);
        }
        if (!$this->formatUrl($request->url)) {
            return response()->json(['error' => ['url' => 'Invalid Url']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $video = Video::find($request->id);
        $video->update(['name_video' => $request->name_video, 'url' => $request->url]);
        return response()->json(['error' => 'Video update'], Response::HTTP_OK);
    }

    public function videoDelete($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        $userVideo = UserVideo::where('id_video', $id)->first();
        VideoPlayList::where('id_user_video', $userVideo->id)->delete();
        $userVideo->delete();
        Video::find($id)->delete();
        return response()->json(['error' => 'Deleted video'], Response::HTTP_OK);
    }

    private function existsId($id)
    {
        $video = Video::find($id);
        return $video === null || empty($id);
    }

    /**
     * Function that validates the format of the url of youtube
     */

    private function formatUrl($url)
    {
        return preg_match_all('#<iframe width="560" height="315" src="https:\/\/www\.youtube\.com\/embed\/(.*)"(?:.*) frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen><\/iframe>#Usm', $url, $matches, PREG_SET_ORDER) === 1;
    }
}
