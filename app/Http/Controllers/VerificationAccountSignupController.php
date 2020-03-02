<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VerificationAccount;

class VerificationAccountSignupController extends Controller
{
    public function verificationAccount()
    {
        $token = request(['token']);
        $payload = auth()->payload($token);
        $id = $payload['sub'];
        $verific = VerificationAccount::where('id_user', $id);
        $verific->update(['activated' => true]);
        return $this->successResponse();
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
            'error' => 'Verification success'
        ], Response::HTTP_OK);
    }
}
