<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResetPassword;
use App\Http\Requests\ChangePasswordRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ChangePasswordController extends Controller
{
    public function process(ChangePasswordRequest $request)
    {
        return $this->getPasswordResetTableRow($request) ? $this->changePassword($request) : $this->tokenNoFoundResponse();
    }

    public function getPasswordResetTableRow($request)
    {
        return ResetPassword::where(['email' => $request->email, 'token' => $request->token]);
    }

    private function tokenNoFoundResponse()
    {
        return response()->json([
            'error' => 'Token or email is incorrecto'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function changePassword($request)
    {
        $user = User::whereEmail($request->email)->first();
        $user->update(['password' => $request->password]);
        $this->getPasswordResetTableRow($request)->delete();
        return response()->json([
            'data' => 'Password successfully change'
        ], Response::HTTP_CREATED);
    }
}
