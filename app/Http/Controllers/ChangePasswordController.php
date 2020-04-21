<?php

namespace App\Http\Controllers;

use App\Models\ResetPassword;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordController extends Controller
{
    public function process(Request $request)
    {
        return ($this->getPasswordResetTableRow($request) && !empty($request->verify)) ? $this->changePassword($request) : $this->tokenNoFoundResponse();
    }

    public function getPasswordResetTableRow($request)
    {
        return ResetPassword::where(['email' => $request->email, 'id_verify' => $request->verify]);
    }

    private function codeVerifyNoFoundResponse()
    {
        return response()->json([
            'error' => 'code verify or email is incorrecto'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function changePassword($request)
    {
        if(!$user = User::whereEmail($request->email)->first()){
            return response()->json([
                'error' => 'Email invalid'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user->update(['password' => $request->password]);
        $this->getPasswordResetTableRow($request)->delete();
        return response()->json([
            'data' => 'Password successfully change'
        ], Response::HTTP_CREATED);
    }
}
