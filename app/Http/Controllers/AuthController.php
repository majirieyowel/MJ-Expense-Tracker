<?php

namespace App\Http\Controllers;

use Google\Client;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\MailgunService;
use App\Http\Requests\SignInRequest;
use App\Http\Requests\SignUpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\GoogleAuthRequest;
use App\Models\EmailQueue;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return $this->ok("Current user", Auth::user());
    }

    public function signUp(SignUpRequest $request)
    {

        $createdUser = User::signUpUser((object)[
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $cacheKey = Str::random(30);
        $cacheValue = $createdUser->id;

        cache([$cacheKey => $cacheValue], now()->addWeek());

        (new MailgunService())->send_with_template("email_verification", [
            "subject" => "Email Verification",
            "to" => $request->username . " " . $request->email,
            "variables" => [
                "verification_url" => "https://spenda.ng/verify/{$cacheKey}",
                "email" => $request->email,
                "contact_url" => "https://spenda.ng/contact-us"
            ]
        ]);

        // Queue welcome email 
        EmailQueue::create([
            "template" => "welcome",
            "email" => $request->email,
            "send_after_ts" => now()->addMinutes(2),
            "data" => [
                "subject" => "Welcome to Spenda",
                "to" => $request->username . " " . $request->email,
                "variables" => [
                    "username" => $request->username,
                    "contact_url" => "https://spenda.ng/contact-us"
                ]
            ]
        ]);

        return $this->signInUser($createdUser);
    }

    public function signIn(SignInRequest $request)
    {

        $user = User::getByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error("Invalid credentials", 401);
        }

        return $this->signInUser($user);
    }

    public function GoogleAuth(GoogleAuthRequest $request)
    {
        try {

            $client = new Client(['client_id' => $request->client_id]);
            $payload = $client->verifyIdToken($request->credential);

            if ($payload) {

                $email = $payload['email'];

                $user = User::getByEmail($request->email);

                if ($user) {
                    return $this->signInUser($user);
                } else {

                    $createdUser = User::signUpUser((object)[
                        'username' => $payload['name'],
                        'email' => $email,
                        'password' => Str::random(),
                    ], true);

                    // Send welcome email 


                    return $this->signInUser($createdUser);
                }
            } else {
                return $this->error("Unable to sign in with google at this time", 400);
            }
        } catch (\Throwable $th) {
            report($th);
            return $this->error("Unable to sign in with google at this time", 400);
        }
    }

    private function signInUser(User $user)
    {

        $userToken = User::signInUser($user);

        $data = [
            "user" => $user,
            "token" => $userToken
        ];

        return $this->ok("User Created", $data);
    }
}
