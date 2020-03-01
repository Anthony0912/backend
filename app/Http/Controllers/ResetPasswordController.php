<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ResetPassword;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function sendEmail(ResetPasswordRequest $request)
    {
        if ($this->validationEmail($request->email)) {
            return $this->failedResponse();
        }
        $this->send($request);
        return $this->successResponse();
    }

    public function send($request)
    {
        $token = $this->createToken($request);
        $data = $this->createEmail($token);
        Mail::to($request->email)->send(new TestEmail($data));
    }

    public function createEmail($token)
    {
        return [
            'token' => $token,
            'subject' => 'Change of password',
            'markdown' => 'Email.ResetPassword'
        ];
    }
    public function createToken($request)
    {
        $oldToken = ResetPassword::where('email', $request->email)->first();
        if ($oldToken) {
            return $oldToken;
        }
        $token = Str::random(60);
        $this->saveToken($request, $token);
        return $token;
    }

    public function saveToken($request, $token)
    {
        ResetPassword::create([
            'email' => $request->email,
            'token' => $token
        ]);
    }

    public function validationEmail($email)
    {
        return !User::where('email', $email)->first();
    }

    public function failedResponse()
    {
        return response()->json([
            'error' => 'Email does\'t found on our database'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successResponse()
    {
        return response()->json([
            'error' => 'Reset email is send successfully, please check your inbox.'
        ], Response::HTTP_OK);
    }
}
