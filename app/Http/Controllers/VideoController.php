<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Models\PlayList;
use App\Models\UserVideoPlayList;
use App\Models\Video;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoController extends Controller
{
    public function showVideos($id)
    {
        $userVideoPlaylist = UserVideoPlayList::where('id_user', $id)->get();
        $object = $this->findVideosAndPlaylist($userVideoPlaylist);
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

    private function findVideosAndPlayList($userVideoPlaylist)
    {
        $idVideos = [];
        $object = [];
        foreach ($userVideoPlaylist as $value) {
            if (!$this->searchId($idVideos, $value->id_video)) {
                $value->video->url = $this->getUrl($value->video->url);
                array_push($object, $this->objectUserVideoPlayList($value->id_user, $value->video, $this->getPlayList($userVideoPlaylist, $value->id_video)));
            }
            array_push($idVideos, $value->id_video);
        }
        return $object;
    }

    private function getUrl($url)
    {
        preg_match('/src="([^"]+)"/', $url, $match);
        return $match[1];
    }

    private function getPlayList($userVideoPlaylist, $id_video)
    {
        $object = [];
        foreach ($userVideoPlaylist as $value) {
            if ($value->id_video === $id_video) {
                array_push($object, $value->playlist);
            }
        }
        return $object;
    }

    public function searchId($idVideos, $id)
    {
        for ($i = 0; $i < count($idVideos); $i++) {
            if ($idVideos[$i] === $id) {
                return true;
            }
        }
        return false;
    }
    public function create(VideoRequest $request)
    {$temp = $request->url;
        if (!$this->formatUrl($temp)) {
            return response()->json(['error' => 'Url invalid'], Response::HTTP_NOT_FOUND);
        }
        $video = Video::create($this->video($request));
        $this->createdGeneralPlayList($request->id_user, $video->id);
        return response()->json(['error' => 'Created video'], Response::HTTP_OK);
    }

    private function createdGeneralPlayList($id_user, $id_video)
    {
        if (!$this->existsGeneralPlayList($id_user)) {
            $playlist = PlayList::create(['name' => 'General']);
            $objectUVP = $this->userVideoPlaylist($id_user, $id_video, $playlist->id);
            UserVideoPlayList::create($objectUVP);
        } else {
            $userVideoPlaylist = UserVideoPlayList::where('id_user', $id_user)->get();
            foreach ($userVideoPlaylist as $value) {
                if ($value->playlist->name === 'General') {
                    $objectUVP = $this->userVideoPlaylist($id_user, $id_video, $value->playlist->id);
                    return UserVideoPlayList::create($objectUVP);
                }
            }
        }
    }

    private function existsGeneralPlayList($id_user)
    {
        return UserVideoPlayList::where('id_user', $id_user)->count() > 0;
    }

    private function userVideoPlaylist($id_user, $id_video, $id_playlist)
    {
        return [
            'id_user' => $id_user,
            'id_video' => $id_video,
            'id_playlist' => $id_playlist,
        ];
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
        request()->validate([
            'name_video' => 'required',
            'url' => 'required',
        ]);
        if (!$this->formatUrl($request->url)) {
            return response()->json(['error' => 'Invalid Url'], Response::HTTP_NOT_FOUND);
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
        UserVideoPlayList::where('id_video', $id)->delete();
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
