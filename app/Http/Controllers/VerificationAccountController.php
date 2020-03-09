<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VerificationAccount;
use Illuminate\Contracts\Encryption\DecryptException;

class VerificationAccountController extends Controller
{
    public function verificationAccount()
    {
        $id = $this->decryptVerify(request('verify'));
        return ($verifyAccount = $this->getVerifyAccount($id)) ? $this->changePermissions($verifyAccount) : $this->failedResponse();
    }

    private function decryptVerify($verify)
    {
        $vfacs = VerificationAccount::all();
        foreach ($vfacs as $vfac) {
            if (md5($vfac->id_user) === $verify) {
                return $vfac->id_user;
            }
        }
    }

    private function getVerifyAccount($id)
    {
        return VerificationAccount::where('id_user', $id)->first();
    }

    private function changePermissions($verifyAccount)
    {
        $verifyAccount->update(['activated' => true]);
        return $this->successResponse();
    }

    private function failedResponse()
    {
        return response()->json([
            'error' => 'Could not make change to database'
        ], Response::HTTP_NOT_FOUND);
    }

    private function successResponse()
    {
        return response()->json([
            'error' => 'Verification success'
        ], Response::HTTP_OK);
    }
}
