<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SignUpRequest;
use Illuminate\Http\Request;
use App\Models\VerificationAccount;
use App\Models\FactorAuthentication;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Crypt;
use DateTime;

class AuthController extends Controller
{

    protected $client;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $this->middleware('auth:api', ['except' => ['login', 'signup']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if (!$token = $this->verifyCredential($request)) {
            return response()->json(
                [
                    'error' => 'Email or password invalid.'
                ], Response::HTTP_NOT_FOUND);
        }
        $id = auth()->user()->id;
        if (!$this->verifyAccountActivated($id)) {
            return response()->json(
                [
                    'error' => 'Please confirm the validation of your account to the email that has been sent to you.'
                ], Response::HTTP_NOT_FOUND);
        }

        if (!$factorAuth = FactorAuthentication::where('id_user', $id)->first()) {
            $verify = $this->createdSms(auth()->user()->code_country, auth()->user()->cellphone);
            FactorAuthentication::create(['id_user' => $id, 'id_verify' => $verify]);
            return response()->json(
                [
                    'id' => $id,
                    'token' => $token
                ], Response::HTTP_CREATED);
        }
    }

    // public function resendSms()
    // {
    //     $factor = $this->findFactorAuthentication($request->data['id']);
    //     $factor->update(['id_verify' => $verify]);
    //     return response()->json(
    //         [
    //             'data' => $id,
    //             'token' => $token
    //         ], Response::HTTP_OK);
    //     return response()->json(['error' => 'Not send verify.'], Response::HTTP_NOT_FOUND);
    // }

    private function verifyCredential($request)
    {
        return auth()->attempt(['email'=>$request->email, 'password' => $request->password]);
    }

    private function verifyAccountActivated($id)
    {
        $vfa = VerificationAccount::where('id_user', $id)->first();
        return $vfa->activated;
    }

    private function createdSms($code_country, $cellphone)
    {
        $verify = Str::random(6);
        $this->client->messages->create(
        '+' . $code_country . $cellphone,
            array(
                'from' => env('TWILIO_SMS'),
                'body' => 'Hi, you tried to log in to youtube kids, use this verify number: '.$verify.'. Have a nice day!'
            )
        );
        return $verify;
    }

    public function signup(SignUpRequest $request)
    {
        if($this->validatedAge($request->birthday) >= 18)
        {
            $user = User::create($request->all());
            $this->createVerifyAccount($user);
            $verify = $this->createCryptVerify($user->id);
            $this->send($user->email, $verify);
            return response()->json([
                'error' => 'Register successfully and email send',
                Response::HTTP_CREATED]);
        }
        return response()->json(
            [
                'error' => 'You are not of legal age, you cannot register.'
            ],
            Response::HTTP_NOT_FOUND);

    }

    private function validatedAge($birthday)
    {
        date_default_timezone_set('America/Costa_Rica');
        $tempBirthday = new DateTime($birthday);
        $now = new DateTime();
        $years = $now->diff($tempBirthday);
        return $years->y;
    }

    private function createVerifyAccount($user)
    {
        return VerificationAccount::create(['id_user' => $user->id ,'email' => $user->email]);
    }

    private function createCryptVerify($id)
    {
        return md5($id);
    }

    private function send($email, $verify)
    {
        $data = $this->createEmail($verify);
        Mail::to($email)->send(new TestEmail($data));
    }

    private function createEmail($verify)
    {
        return [
            'verify' => $verify,
            'subject' => 'Link Verification Account',
            'markdown' => 'Email.VerificationAccount'
        ];
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
            'error' => 'successfully.'
        ], Response::HTTP_OK);
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
}
