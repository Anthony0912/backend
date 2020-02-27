<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Requests\VideoRequest;

class VideoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', 
        // ['except' => 
        //     [
        //         'showVideos', 
        //         'create', 
        //         'formVideoEdit', 
        //         'update',
        //         'delete'
        //     ]
        // ]);
    }

    public function showVideos()
    {
        $video = Video::all();
        return $video != null ? 
            response()->json(['Ok' => 200, 'video' => $video]) : 
            response('No exist data', 404);
    }

    public function create(VideoRequest $request)
    {
        Video::create($request->all());
        return response('Ok', 200);
    }

    public function formVideoEdit($id)
    {
        if ($this->existsId($id)) {
            return response('No exist id', 404);
        }
        $video = Video::find($id);
        return response()->json(['Ok' => 200, 'video' => $video]);
    }

    public function update(VideoRequest $request, $id)
    {
        if ($this->existsId($id)) {
            return response('No exist id', 404);
        }
        $video = Video::find($id);
        $video->update($request->all());
        return response('Ok', 201);
    }

    public function delete($id)
    {
        if ($this->existsId($id)) {
            return response('No exist id', 404);
        }
        Video::find($id)->delete();
        return response('Ok', 200);
    }
    
    private function existsId($id)
    {
        $video = Video::find($id);
        return $video === null || empty($id);
    }
}