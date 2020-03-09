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
        if (!$this->validationEmail($request->email)) {
            return $this->failedResponse();
        }
        $verify = $this->createVerify($request->email);
        $this->send($request->email, $verify);
        return $this->successResponse();
    }

    private function validationEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function send($email, $verify)
    {
        $data = $this->createEmail($verify);
        Mail::to($email)->send(new TestEmail($data));
    }

    private function createEmail($verify)
    {
        return [
            'verify' => $verify,
            'subject' => 'Change of password',
            'markdown' => 'Email.ResetPassword'
        ];
    }

    private function createVerify($email)
    {
        if ($oldVerify = ResetPassword::where('email', $email)->first()) {
            return $oldVerify->id_verify;
        }
        $verify = Str::random(100);
        $this->saveVerify($email, $verify);
        return $verify;
    }

    private function saveVerify($email, $verify)
    {
        ResetPassword::create([
            'email' => $email,
            'id_verify' => $verify
        ]);
    }

    private function failedResponse()
    {
        return response()->json([
            'error' => 'Email does\'t found on our database'
        ], Response::HTTP_NOT_FOUND);
    }

    private function successResponse()
    {
        return response()->json([
            'error' => 'Reset email is send successfully, please check your inbox.'
        ], Response::HTTP_OK);
    }
}
