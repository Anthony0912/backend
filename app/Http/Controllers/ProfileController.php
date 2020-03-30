<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use App\Models\UserProfile;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function profileCreate(ProfileRequest $request)
    {
        $profile = Profile::create($request->except(['id_user']));
        UserProfile::create(['id_user' => $request->id_user, 'id_profile' => $profile->id]);
        return response()->json(['error' => 'Created profile'], Response::HTTP_OK);
    }

    public function profileShow($id)
    {
        $profiles = UserProfile::join('profiles', 'profiles.id', '=', 'user_profiles.id_profile')
            ->where('user_profiles.id_user', '=', $id)
            ->get();
        return response()->json($profiles, Response::HTTP_OK);

    }

    public function profileChangeStatus($id)
    {
        $profile = Profile::find($id);
        $profile->status = $profile->status ? false : true;
        $profile->save();
        return response()->json(['status' => $profile->status], Response::HTTP_OK);
    }

    public function profileEdit($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        $profile = Profile::find($id);
        return response()->json($profile, Response::HTTP_OK);
    }

     public function profileUpdate(Request $request)
    {
        if ($this->existsId($request->id_profile)) {
            return response()->json(['error' => 'ID video not exist'], Response::HTTP_NOT_FOUND);
        }
        request()->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'birthday' => 'required|date',
            'username' => ['required', Rule::unique('profiles')->ignore($request->id_profile)]
        ]);

        $profile = Profile::find($request->id_profile);
        $profile->update($request->except(['id_profile']));
        return response()->json(['error' => 'Video update'], Response::HTTP_OK);
    }

    public function profileDelete($id)
    {
        if ($this->existsId($id)) {
            return response()->json(['error' => 'No exist id'], Response::HTTP_NOT_FOUND);
        }
        UserProfile::where('id_profile', $id)->delete();
        Profile::where('id', $id)->delete();
        return response()->json(['error' => 'Deleted video'], Response::HTTP_OK);
    }

    public function profilePasswordReset(Request $request)
    {
        request()->validate([
            'username' => ['required', Rule::unique('profiles')->ignore($request->username, 'username')],
            'password' => 'required|numeric|confirmed|digits_between:6,6'
        ]);

        $profile = Profile::where('username', $request->username)->first();
        if ($profile === null) {
            return response()->json(['error' => 'username invalid'], Response::HTTP_NOT_FOUND);
        }else{
            $profile->update(['password' => $request->password]);
            return response()->json(['error' => 'Password profile change'], Response::HTTP_OK);
        }
        return response()->json(['error' => 'Not update'], Response::HTTP_NOT_FOUND);
    }

    private function existsId($id)
    {
        $profile = Profile::find($id);
        return $profile === null || empty($id);
    }
}
