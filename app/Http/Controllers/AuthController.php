<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Validation\Rule;
use App\Http\Requests\SignUpRequest;
use App\Mail\TestEmail;
use App\Models\FactorAuthentication;
use App\Models\User;
use App\Models\VerificationAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Rest\Client;
use App\Models\Country;

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
        $this->middleware('auth:api', ['except' => [
            'login',
            'signup',
            'resendSms',
            'getCodeCountries',
            'settingAccountEdit',
            'settingAccountUpdate',
            'findFactorAuthentication',
        ]]);
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
                    'error' => 'Email or password invalid.',
                ],
                Response::HTTP_NOT_FOUND
            );
        }
        $id = auth()->user()->id;
        if (!$this->verifyAccountActivated($id)) {
            return response()->json(
                [
                    'error' => 'Please confirm the validation of your account to the email that has been sent to you.',
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $verify = $this->createVerifySms();
        if (!$factorAuth = $this->findFactorAuthentication($id)) {
            FactorAuthentication::create(['id_user' => $id, 'id_verify' => $verify]);
            $this->createdSms(auth()->user()->code_country, auth()->user()->cellphone, $verify);
            return response()->json(
                [
                    'id' => $id,
                    'token' => $token,
                ],
                Response::HTTP_CREATED
            );
        } else {
            $this->updateCodeFactorAuthentication($factorAuth, $verify);
            $this->createdSms(auth()->user()->code_country, auth()->user()->cellphone, $verify);
            return response()->json(
                [
                    'id' => $id,
                    'token' => $token,
                ],
                Response::HTTP_CREATED
            );
        }
    }
    public function settingAccountEdit($id)
    {
        $user = User::find($id);
        return response()->json($user, Response::HTTP_OK);
    }

    public function settingAccountUpdate(Request $request)
    {
        if ($this->existsId($request->id)) {
            return response()->json(['error' => 'ID user not exist'], Response::HTTP_NOT_FOUND);
        }
        request()->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'country' => 'required|string',
            'code_country' => 'required|integer',
            'cellphone' => 'required|integer',
            'birthday' => 'required|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->id)]
        ]);

        $user = User::find($request->id);
        $user->update($request->except(['id']));
        return response()->json(['error' => 'Playlist update'], Response::HTTP_OK);
    }

    public function resendSms(Request $request)
    {
        $id = $request->data['id'];
        $verify = $this->createVerifySms();
        $factor = $this->findFactorAuthentication($id);
        if ($this->updateCodeFactorAuthentication($factor, $verify)) {
            $user = User::find($id);
            $this->createdSms($user->code_country, $user->cellphone, $verify);
            return response()->json(
                [
                    'error' => 'Code update',
                ],
                Response::HTTP_OK
            );
        }
        return response()->json(['error' => 'Not send verify.'], Response::HTTP_NOT_FOUND);
    }

    private function existsId($id)
    {
        $user = User::find($id);
        return $user === null || empty($id);
    }

    private function updateCodeFactorAuthentication($factor, $verify)
    {
        $factor->fill(['id_verify' => $verify]);
        return $factor->update();
    }

    private function findFactorAuthentication($id)
    {
        return FactorAuthentication::where('id_user', $id)->first();
    }

    private function verifyCredential($request)
    {
        return auth()->attempt(['email' => $request->email, 'password' => $request->password]);
    }

    private function verifyAccountActivated($id)
    {
        $vfa = VerificationAccount::where('id_user', $id)->first();
        return $vfa->activated;
    }

    private function createdSms($code_country, $cellphone, $verify)
    {
        $this->client->messages->create(
            '+' . $code_country . $cellphone,
            array(
                'from' => env('TWILIO_SMS'),
                'body' => 'Hi, you tried to log in to youtube kids, use this verify number: ' . $verify . '. Have a nice day!',
            )
        );
    }

    private function createVerifySms()
    {
        return Str::random(6);
    }

    public function signup(SignUpRequest $request)
    {
        if ($this->validatedAge($request->birthday) >= 18) {
            $user = User::create($request->all());
            $this->createVerifyAccount($user);
            $verify = $this->createCryptVerify($user->id);
            $this->send($user->email, $verify);
            return response()->json([
                'error' => 'Register successfully and email send',
            ], Response::HTTP_CREATED);
        }
        return response()->json(
            [
                'error' => 'You are not of legal age, you cannot register.',
            ],
            Response::HTTP_NOT_FOUND
        );
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
        return VerificationAccount::create(['id_user' => $user->id, 'email' => $user->email]);
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
            'markdown' => 'Email.VerificationAccount',
        ];
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
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function refresh()
    {
        return $this->respondWithToken(auth()->$this->refresh());
    }

    /**
     * Log the user out ( Invalidate the token ).
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function getCodeCountries()
    {
        $countries = Country::all();
        return response()->json($countries, Response::HTTP_OK);
    }
}
