<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;
use App\Models\VerificationAccount;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'signup']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $verification = VerificationAccount::where('email', $credentials['email'])->first();
        if(!$verification->activated)
        {
            return response()->json(['error' => 'Please vefication account in your email'], 401);
        }
        if (!$token = auth()->attempt($credentials))
        {
            return response()->json(['error' => 'Email or password does\'t exits'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function signup(SignUpRequest $request)
    {
        $user = User::create($request->all());
        VerificationAccount::create(['id_user' => $user['id'],'email' => $request->email]);
        $credentials = ['email' => $request->email, 'password' => $request->password];
        $token = auth()->attempt($credentials);
        $this->send($request, $token);
        return $this->respondWithToken($token);
    }

    public function send($request,$token)
    {
        $data = $this->createEmail($token);
        Mail::to($request->email)->send(new TestEmail($data));
    }

    public function createEmail($token)
    {
        return [
            'token' => $token,
            'subject' => 'Link Verification Account',
            'markdown' => 'Email.VerificationAccount'
        ];
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

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * (60 * 60),
            'user' => auth()->user()->id
        ]);
    }
}
