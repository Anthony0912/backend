<?php

namespace App\Http\Controllers;

use App\Models\FactorAuthentication;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\FactorAuthenticationRequest;

class FactorAuthenticationController extends Controller
{
    public function factorAuthentication(FactorAuthenticationRequest $request)
    {
        $factor = $this->findFactorAuthentication($request->data['id']);
        if ($factor->id_verify !== $request->id_verify)
        {
            return response()->json([
            'error' => 'Code invalid'
            ], Response::HTTP_NOT_FOUND);
        }
        $factor->delete();
        return $this->respondWithToken($request->data['token'], $request);
    }

    private function findFactorAuthentication($id)
    {
        return FactorAuthentication::where('id_user', $id)->first();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $request)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * (60 * 60),
            'user' => $request->id,
        ]);
    }

}
